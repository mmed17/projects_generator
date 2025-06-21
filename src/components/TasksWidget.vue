<template>
    <div id="projectcreatoraio" class="tasks-widget">
        <div :style="styles.filterContainer">
            <div :style="styles.filter">
                <NcTextField :style="styles.noMargin" v-model="searchQuery"
                    :label="t('projectcreatoraio', 'Search Tasks')"
                    :placeholder="t('projectcreatoraio', 'e.g: Buy a new ...')"
                    trailing-button-icon="close"
                    @input="searchTasks">
                    <template #icon>
                        <Magnify :size="20" />
                    </template>
                </NcTextField>
                
                <NcActions :persistent="true">
                    <template #icon>
                        <FilterCog :size="20" />
                    </template>

                    <NcActionInput
                        v-model="selectedUser"
                        type="multiselect"
                        track-by="label"
                        :multiple="false"
                        :options="allUsers"
                        :loading="isFetchingUsers"
                        :append-to-body="true"
                        @search="fetchUsers"
                        @update:modelValue="handleUserSelection">
                        <template #icon>
                            <Account :size="20" />
                        </template>
                        Select a user
                    </NcActionInput>

                    <NcActionInput
                        v-model="selectedProject"
                        type="multiselect"
                        track-by="label"
                        :multiple="false"
                        :options="allProjects"
                        :loading="isFetchingProjects"
                        :append-to-body="true"
                        @update:modelValue="handleProjectSelection">
                        <template #icon>
                            <Folder :size="20" />
                        </template>
                        Select a project
                    </NcActionInput>

                    <NcActionRadio
                        v-for="type in allTaskTypes"
                        :key="type.id"
                        v-model="selectedTaskType"
                        :value="type.id"
                        name="task-type-selection">
                        {{ type.label }}
                    </NcActionRadio>
                </NcActions>
            </div>
        </div>

        <div v-if="loading" class="loading-placeholder">
            <NcLoadingIcon :size="44" />
        </div>
        <NcEmptyContent v-else-if="tasks.length === 0"
            :name="t('projectcreatoraio', 'No Tasks found')">
            <template #icon>
                <NcIconSvgWrapper :svg="folderSvg" />
            </template>
        </NcEmptyContent>

        <div v-else class="tasks-list">
            <ul>
                <NcListItem
                    v-for="task in filteredTasks"
                    :name="task.title"
                    :active="selectedTaskId === task.id"
                    :force-display-actions="true"
                    @click="selectTask(task)">
                    <template #icon>
                        <CalendarClockOutline :size="25" />
                    </template>
                    
                    <template #subname></template>

                    <template #actions>
                        <NcActionButton
                            icon="icon-download"
                            :title="t('projectcreatoraio', 'Download Task')">
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
import { NcActions, NcActionButton, NcEmptyContent, NcNoteCard, NcTextField, NcSelect } from '@nextcloud/vue'
import NcListItem from '@nextcloud/vue/components/NcListItem'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcIconSvgWrapper from '@nextcloud/vue/components/NcIconSvgWrapper'
import NcUserBubble from '@nextcloud/vue/components/NcUserBubble'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcActionInput from '@nextcloud/vue/components/NcActionInput'
import NcActionCheckbox from '@nextcloud/vue/components/NcActionCheckbox'
import NcPopover from '@nextcloud/vue/components/NcPopover'
import NcActionRadio from '@nextcloud/vue/components/NcActionRadio'
import { t } from '@nextcloud/l10n'

import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import Folder from 'vue-material-design-icons/Folder.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import Download from 'vue-material-design-icons/Download.vue';
import Details from 'vue-material-design-icons/Details.vue';
import FilterCog from 'vue-material-design-icons/FilterCog.vue';
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue';
import Account from 'vue-material-design-icons/Account.vue';
import CalendarClockOutline from 'vue-material-design-icons/CalendarClockOutline.vue';
import AccountEdit from "vue-material-design-icons/AccountEdit.vue";
import Label from "vue-material-design-icons/Label.vue";
import NcAvatar from '@nextcloud/vue/components/NcAvatar';
import NcChip from '@nextcloud/vue/components/NcChip';

import { mdiClock, mdiFolder } from '@mdi/js';

import UsersFetcher from './UsersFetcher.vue';
import { UsersSerice } from '../Services/users';
import { ProjectsService } from '../Services/projects';
import { loadState } from '@nextcloud/initial-state';
import { APP_ID } from '../macros/app-id';
import { generateUrl } from '@nextcloud/router'

const currentUser = loadState(APP_ID, 'currentUser');
const usersService = UsersSerice.getInstance();
const projectsService = ProjectsService.getInstance();

const styles = {
    filterContainer: {
        'margin': '10px 0px' 
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
	name: 'TasksWidget',
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
        mdiFolder,
        mdiClock,
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
        NcAvatar,
        Folder,
        Label,
        NcChip,
        NcActionCheckbox,
        NcPopover,
        CalendarClockOutline,
        NcSelect,
        NcActionRadio
	},
	data() {
		return {
			t,
			tasks: [],
			loading: false,
			searchQuery: '',
            folderSvg: mdiFolder,
            clockSvg: mdiClock,
            selectedTaskId: '',
            showFilterDialog: false,
            isFetchingUsers: false,
            isFetchingProjects: false,
            selectedUser: null,
            selectedProject: null,
            selectedTaskType: 0,
            styles,
            allUsers: [],
            allProjects: [],
            allTaskTypes: [
                { id: 0, label: 'Open Tasks', endpoint: 'open' },
                { id: 1, label: 'Upcoming Tasks', endpoint: 'upcoming' },
                { id: 2, label: 'Overdue Tasks', endpoint: 'overdue' }
            ],
            searchTimeout: undefined,
		}
	},
	computed: {
		filteredTasks() {
            console.log(this.searchQuery, this.tasks);
			if (!this.searchQuery) {
				return this.tasks;
			}

			return this.tasks.filter(task => {
				return task.title.toLowerCase().includes(this.searchQuery.toLowerCase());
			});
		},
	},
    watch: {
        selectedUser() {
            this.fetchTasks();
        },
        selectedProject() {
            this.fetchTasks();
        },
        selectedTaskType() {
            this.fetchTasks();
        }
    },
	async mounted() {
        const panelContent = this.$el.closest('.panel--content');
        if (panelContent) {
            panelContent.style.overflowY = 'scroll';
        }
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
        getTaskTypeLabel(id) {
            const type = this.allTaskTypes.find(t => t.id === id)
            return type ? type.label : 'Unknown'
        },
        getTaskTypeVariant(id) {
            switch (id) {
                case 0: return 'primary'
                case 1: return 'success'
                case 2: return 'error'
                default: return 'default'
            }
        },
        async fetchTasks() {
            if (!this.selectedUser && !this.selectedProject) {
                this.tasks = [];
                return;
            }

            this.loading = true;

            try {
                const taskType = this.allTaskTypes.find(t => t.id === this.selectedTaskType);
                if (!taskType) {
                    console.error('Invalid task type selected');
                    this.tasks = [];
                    return;
                }

                const params = new URLSearchParams();
                if (this.selectedUser) {
                    params.append('userId', this.selectedUser.id);
                }
                if (this.selectedProject) {
                    params.append('projectId', this.selectedProject.id);
                }

                const url = generateUrl(`/apps/${APP_ID}/api/v1/tasks/${taskType.endpoint}`);
                const response = await fetch(`${url}?${params.toString()}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.tasks = data;

            } catch (error) {
                console.error('Failed to fetch tasks:', error);
                this.tasks = [];
            } finally {
                this.loading = false;
            }
        },
        async handleUserSelection(user) {
            this.selectedUser = user;

            if (!user) {
                this.allProjects = [];
                this.selectedProject = null; // Clear selected project
                return;
            }

            this.isFetchingProjects = true;
            this.allProjects = await projectsService.fetchProjectsByUser(user.id);
            this.allProjects = this.allProjects.map((p) => {
                p.label = p.name;
                return p;
            });
            // if the previously selected project is not in the new list, clear it
            if (this.selectedProject && !this.allProjects.some(p => p.id === this.selectedProject.id)) {
                this.selectedProject = null;
            }
            this.isFetchingProjects = false;
        },
        handleProjectSelection(project) {
            // v-model handles the update, the watch block will trigger the fetch
            this.selectedProject = project;
        },
        selectTask(task) {
            this.selectedTaskId = task.id;
            const url = generateUrl(`/apps/deck/board/${task.board_id}/card/${task.id}`);
            window.open(url, '_blank');
            console.log(task);
        },
	},
}
</script>

<style lang="css" scoped></style>