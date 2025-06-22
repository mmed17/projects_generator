<template>
    <div id="projectcreatoraio" class="project-widget">
        <div :style="styles.filterContainer">
            <div :style="styles.filter">
                <NcTextField :style="styles.noMargin" v-model="searchQuery"
                    :label="t('projectcreatoraio', 'Search Projects')"
                    :placeholder="t('projectcreatoraio', 'e.g: Project Alpha...')"
                    trailing-button-icon="close">
                    <template #icon>
                        <Magnify :size="20" />
                    </template>
                </NcTextField>
                
                <NcActions>
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

                    <template #actions>
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
                    </template>
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
import Details from 'vue-material-design-icons/Details.vue';
import FilterCog from 'vue-material-design-icons/FilterCog.vue';
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue';
import Account from 'vue-material-design-icons/Account.vue';
import AccountEdit from "vue-material-design-icons/AccountEdit.vue";
import NcAvatar from '@nextcloud/vue/components/NcAvatar';

import folderSvg from '@mdi/svg/svg/folder.svg?raw'
import UsersFetcher from './UsersFetcher.vue'
import { PROJECT_TYPES } from '../macros/project-types';
import { UsersSerice } from '../Services/users'
import { ProjectsService } from '../Services/projects'
import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import { APP_ID } from '../macros/app-id'

const currentUser = loadState(APP_ID, 'currentUser');
const usersService = UsersSerice.getInstance();
const projectsService = ProjectsService.getInstance();

const styles = {
    filterContainer: {
        'margin': '10px 5px' 
    },
    filter: {
        'display': 'flex',
        'align-items': 'center',
        'justify-content': 'space-between',
        'gap': '10px'
    },
    noMargin: {
        'margin': '0px'
    }
};

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
			searchQuery: '',
            folderSvg,
            selectedProjectId: '',
            showFilterDialog: false,
            isFetchingUsers: false,
            selectedUser: null,
            PROJECT_TYPES,
            styles,
            allUsers: [],
            searchTimeout: undefined
		}
	},
	computed: {
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

        await this.fetchProjectsByUser();
	},
	methods: {
        async fetchProjectsByUser(user) {
            this.loading = true;
            this.projects = await projectsService.fetchProjectsByUser(user ? user.id:currentUser.id);
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
            this.selectedProjectId = project.id;
            const url = generateUrl(`/apps/contacts/circle/${project.circleId}`);
            window.open(url, '_blank');
        },
        closeFilterDialog() {
            setTimeout(() => {
                this.showFilterDialog = false
            }, 200);
        },
		downloadFile(url) {},
	},
}
</script>

<style lang="css" scoped>
.project-widget-filter {
  
}
</style>