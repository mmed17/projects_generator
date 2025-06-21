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
                
                <!-- <NcActions :persistent="true">
                    <template #icon>
                        <FilterCog :size="20" />
                    </template>
                    <NcSelect
                        v-model="selectedUser"
                        type="multiselect"
                        track-by="label"
                        :append-to-body="true"
                        :multiple="false"
                        :options="allUsers"
                        :loading="isFetchingUsers"
                        @search="fetchUsers"
                        @update:modelValue="handleUserSelection">
                        <template #icon>
                            <Account :size="20" />
                        </template>
                        Select a user
                    </NcSelect>

                    <NcSelect
                        v-model="selectedProject"
                        type="multiselect"
                        track-by="label"
                        :append-to-body="true"
                        :multiple="false"
                        :options="allProjects"
                        :loading="isFetchingProjects"
                        @update:modelValue="handleProjectSelection">
                        <template #icon>
                            <Folder :size="20" />
                        </template>
                        Select a project
                    </NcSelect>

                    <NcActionCheckbox
                        v-for="type in allTaskTypes"
                        :key="type.id"
                        :model-value="selectedTaskTypes.includes(type.id)"
                        @change="toggleTaskType(type.id)">
                        {{ type.label }}
                    </NcActionCheckbox>
                </NcActions> -->
                <NcPopover 
                    popup-role="dialog" 
                    :shown="true" 
                    @update:shown="handleFilterPopover">
                    <template #trigger>
                        <NcButton>I am the trigger</NcButton>
                    </template>
                    <template #default>
                        <form tabindex="0" role="dialog" aria-labelledby="popover-example-dialog-header-1" @submit.prevent>
                            <h2 id="popover-example-dialog-header-1">this is some content</h2>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. <br/>
                                Vestibulum eget placerat velit.
                            </p>
                            <label>
                                Label element
                                <input type="text" placeholder="input element"/>
                            </label>
                        </form>
                    </template>
                </NcPopover>
            </div>

            <div style="display: flex; justify-content: start; align-items: center; gap: 10px; margin-top: 5px">
                <NcChip v-if="selectedUser" no-close>
                    <template #icon>
                        <NcAvatar 
                            :size="24" 
                            :user="selectedUser.id" 
                            :display-name="selectedUser.displayName"/>
                    </template>
                    {{ selectedUser.displayName }}
                </NcChip>
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
                        <CalendarClockOutline :size="30" />
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
        NcSelect
	},
    handleFilterPopover(event) {
        console.log("handle popover", event);
    },
	data() {
		return {
			t,
			tasks: [{  
                "title": "Test card",
                "description": "A card description",
                "type": "plain",
                "order": 999,
                "duedate": "2019-12-24T19:29:30+00:00",
            }],
			loading: true,
			searchQuery: '',
            folderSvg: mdiFolder,
            clockSvg: mdiClock,
            selectedTaskId: '',
            showFilterDialog: false,
            isFetchingUsers: false,
            isFetchingProjects: false,
            selectedUser: null,
            selectedProject: null,
            selectedTaskTypes: [],
            styles,
            allUsers: [],
            allProjects: [],
            allTaskTypes: [
                { id: 0, label: 'Open Tasks' },
                { id: 1, label: 'Upcoming Tasks' },
                { id: 2, label: 'Overdue Tasks' }
            ],
            searchTimeout: undefined,
		}
	},
	computed: {
		filteredTasks() {
			if (!this.searchQuery) {
				return this.tasks;
			}

			return this.tasks.filter(task => {
				return task.title.toLowerCase().includes(this.searchQuery.toLowerCase());
			});
		},
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
        async fetchTasks() {

        },
		async downloadFile(url) {},
        toggleTaskType(typeId) {
            const index = this.selectedTaskTypes.indexOf(typeId);
            if (index > -1) {
            this.selectedTaskTypes.splice(index, 1);
            } else {
            this.selectedTaskTypes.push(typeId);
            }
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
        async handleUserSelection(user) {
            this.selectedUser = user;

            if(!user) {
                this.projects = [];
                return;
            }

            this.allProjects = await projectsService.fetchProjectsByUser(user.id);
            this.allProjects.map((p) => {
                p.label = p.name;
                if(this.selectedProject && p.id === this.selectedProject.id) {
                    this.selectedProject = '';
                }
            });
        },
        async handleProjectSelection() {

        },
        selectTask() {

        }
	},
}
</script>

<style lang="css" scoped>
</style>