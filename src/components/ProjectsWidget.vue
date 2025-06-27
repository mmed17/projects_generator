<template>
    <div class="project-widget">
        <div class="filter-container">
            <NcTextField v-model="searchQuery"
                :label="t('projectcreatoraio', 'Search Projects')"
                :placeholder="t('projectcreatoraio', 'e.g: Project Alpha...')"
                trailing-button-icon="close">
                <template #icon>
                    <Magnify :size="20" />
                </template>
            </NcTextField>
            
            <NcActions v-if="isAdmin">
                <template #icon>
                    <NcAvatar 
                        v-if="selectedUser" 
                        :display-name="selectedUser.displayName" 
                        :is-no-user="true" 
                        size="32" />
                    <AccountPlus v-else :size="20" />
                </template>

                <NcActionInput
                    v-model="selectedUser"
                    ref="usersInputRef"
                    type="multiselect"
                    track-by="label"
                    :append-to-body="true"
                    :multiple="false"
                    :options="allUsers"
                    :loading="isFetchingUsers"
                    @search="fetchUsers"
                    @update:modelValue="fetchProjectsByUser">
                    <template #icon>
                        <Account :size="20" />
                    </template>
                    Please select a user
                </NcActionInput>
            </NcActions>
        </div>

        <div v-if="loading" class="loading-placeholder">
            <NcLoadingIcon :size="44" />
        </div>

        <NcEmptyContent v-else-if="projects.length === 0"
            :name="t('projectcreatoraio', 'No projects found')">
            <template #icon>
                <NcIconSvgWrapper :svg="folderSvg" />
            </template>
        </NcEmptyContent>

        <div v-else class="project-list">
            <ul>
                <NcListItem
                    v-for="project in filteredProjects"
                    :name="project.name"
                    :active="selectedProjectId === project.id"
                    :force-display-actions="true"
                    @click="selectProject(project)">
                    <template #icon>
                        <FolderOutline :size="30" />
                    </template>
                    
                    <template #subname>
                        {{ PROJECT_TYPES[project.type].label }}
                    </template>

                    <template #extra-actions>
                        <NcButton variant="tertiary-no-background" @click="onPreview(project)">
                            <template #icon>
                                <NcLoadingIcon v-if="isPreviewing" :size="20" />
                                <EyeOutline v-else :size="20" />
                            </template>
                        </NcButton>
                        <NcButton variant="tertiary-no-background" @click="onDownload(project)">
                            <template #icon>
                                <Download :size="20" />
                            </template>
                        </NcButton>
                    </template>
                    <!-- <template #actions>
                        <NcActionButton
                            icon="icon-download"
                            :title="t('projectcreatoraio', 'Download project')"
                            @click="downloadFile(project.folderName)">
                            Download
                        </NcActionButton>
                        <NcActionButton
                            icon="icon-details"
                            :title="t('projectcreatoraio', 'View details')">
                            View Details
                        </NcActionButton>
                    </template> -->
                </NcListItem>
            </ul>
        </div>
    </div>
</template>

<script>
import { NcActions, NcActionButton, NcEmptyContent, NcNoteCard, NcTextField } from '@nextcloud/vue'
import NcListItem from '@nextcloud/vue/components/NcListItem'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcIconSvgWrapper from '@nextcloud/vue/components/NcIconSvgWrapper'
import NcUserBubble from '@nextcloud/vue/components/NcUserBubble'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcActionInput from '@nextcloud/vue/components/NcActionInput'
import { t } from '@nextcloud/l10n'

import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Download from 'vue-material-design-icons/Download.vue';
import EyeOutline from 'vue-material-design-icons/EyeOutline.vue';
import Details from 'vue-material-design-icons/Details.vue';
import FilterCog from 'vue-material-design-icons/FilterCog.vue';
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue';
import Account from 'vue-material-design-icons/Account.vue';
import AccountEdit from "vue-material-design-icons/AccountEdit.vue";
import NcAvatar from '@nextcloud/vue/components/NcAvatar';
import { getCurrentUser } from '@nextcloud/auth'

import folderSvg from '@mdi/svg/svg/folder.svg?raw'
import UsersFetcher from './UsersFetcher.vue'
import { PROJECT_TYPES } from '../macros/project-types';
import { UsersSerice } from '../Services/users'
import { ProjectsService } from '../Services/projects'
import { generateUrl } from '@nextcloud/router'

const usersService = UsersSerice.getInstance();
const projectsService = ProjectsService.getInstance();

export default {
	name: 'ProjectsWidget',
	components: {
		NcActions,
		NcActionButton,
		NcEmptyContent,
		NcNoteCard,
		NcLoadingIcon,
		NcTextField,
		FolderOutline,
        NcIconSvgWrapper,
        Magnify,
        folderSvg,
        NcListItem,
        Download,
        Details,
        NcUserBubble,
        NcDialog,
        NcButton,
        FilterCog,
        UsersFetcher,
        NcActionInput,
        AccountPlus,
        Account,
        AccountEdit,
        NcAvatar
	},
	data() {
		return {
			t,
			projects: [],
			loading: true,
			searchQuery: null,
            folderSvg,
            selectedProjectId: null,
            showFilterDialog: false,
            isFetchingUsers: false,
            selectedUser: null,
            PROJECT_TYPES,
            allUsers: [],
            searchTimeout: undefined
		}
	},
	computed: {
        isAdmin() {
            return !!getCurrentUser()?.isAdmin;
        },
		filteredProjects() {
			if (!this.searchQuery) {
				return this.projects;
			}

			return this.projects.filter(project => {
				return project.name.toLowerCase().includes(this.searchQuery.toLowerCase());
			});
		},
	},
	async mounted() {
        const panelContent = this.$el.closest('.panel--content');
        if (panelContent) {
            panelContent.style.overflowY = 'scroll';
        }

        await this.listProjects();
	},
	methods: {
        async listProjects() {
            this.loading = true;
            this.projects = await projectsService.list();
            this.loading = false;
        },
        async fetchUsers(query) {
			if (this.searchTimeout) {
				clearTimeout(this.searchTimeout);
			}

			this.isFetchingUsers = true;

			this.searchTimeout = setTimeout(async () => {
                this.allUsers = await usersService.search(query);
                this.isFetchingUsers = false;
			}, 300);
		},
        selectProject(project) {
            let eventPayload = null;

            if (this.selectedProjectId === project.id) {
                this.selectedProjectId = null;
            } else {
                this.selectedProjectId = project.id;
                eventPayload = {
                    projectId: project.id,
                    boardId: project.boardId
                };
            }

            const event = new CustomEvent('projectcreatoraio:project-selected', { detail: eventPayload });
            document.dispatchEvent(event);
        },
        navigateToFolder(id, name) {
            const url = generateUrl(`/apps/files/files/${id}?dir=/${name}`);
            open(url, "_blank");
        },
        onPreview(project) {
            this.navigateToFolder(project.id, project.label);
        },
        onDownload(project) {

        },
        triggerDownload(href, filename = null) {
            const link = document.createElement('a');
            link.href = href;
            if (filename) link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            link.remove();
        },
        normalizedPath(path) {
            const parts = path.split('/');
            if (parts.length >= 3) {
                [parts[1], parts[2]] = [parts[2], parts[1]];
            }
            return parts.join('/');
        }
	},
}
</script>

<style lang="css" scoped>
@import '../styles/dashboard.css';
</style>