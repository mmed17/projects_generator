<template>
    <div class="project-widget">
        <NcTextField v-model="searchQuery"
            :label="t('your_app_name', 'Search Projects')"
            :placeholder="t('your_app_name', 'e.g: Project Alpha...')"
            trailing-button-icon="close"
            @input="searchProjects">
            <template #icon>
                <Magnify :size="20" />
            </template>
        </NcTextField>

        <div v-if="loading" class="loading-placeholder">
            <NcLoadingIcon :size="44" />
        </div>
        <NcEmptyContent v-else-if="projects.length === 0"
            :name="t('your_app_name', 'No projects found')">
            <template #icon>
                <NcIconSvgWrapper :svg="folderSvg" />
            </template>
        </NcEmptyContent>

        <div v-else class="project-list">
            <ul>
                <NcListItem
                    v-for="project in filteredProjects"
                    :name="project.name"
                    :force-display-actions="true">
                    <template #icon>
                        <FolderOutline :size="30" />
                    </template>
                    
                    <template #subname>
                        {{ types[project.type].label }}
                    </template>

                    <template #actions>
                        <NcActionButton
                            icon="icon-download"
                            :title="t('your_app_name', 'Download project')"
                            @click="downloadFile(project.downloadUrl)">
                        </NcActionButton>
                        <NcActionButton
                            icon="icon-details"
                            :title="t('your_app_name', 'View details')">
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

import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'

import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Download from 'vue-material-design-icons/Download.vue';
import Details from 'vue-material-design-icons/Details.vue';
import folderSvg from '@mdi/svg/svg/folder.svg?raw'
import axios from 'axios';

const types = [
	{ id: 0, label: 'Marketing Campaign' },
	{ id: 1, label: 'Product Development' },
	{ id: 2, label: 'Research Project' },
	{ id: 3, label: 'Event Planning' },
	{ id: 4, label: 'Consulting Engagement' },
	{ id: 5, label: 'Training Program' },
	{ id: 6, label: 'Software Development' },
	{ id: 7, label: 'Infrastructure Upgrade' },
	{ id: 8, label: 'Community Outreach' },
	{ id: 9, label: 'Other' }
];

export default {
	name: 'ProjectWidget',
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
        Details
	},
	data() {
		return {
			projects: [],
			loading: true,
			searchQuery: '',
			t,
            folderSvg,
            types
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

        await this.fetchProjects();
	},
	methods: {
		async fetchProjects() {
			this.loading = true
			try {
				const url = generateUrl('/apps/projectcreatoraio/api/v1/projects/list')
				const response = await axios.get(url, {
                    headers: {
						'OCS-APIRequest': 'true',
						'Content-Type': 'application/json'
					}
                });

				this.projects = response.data ?? [];
                
                console.log(this.projects);

			} catch (e) {
				console.error('Failed to fetch projects:', e);
			} finally {
				this.loading = false
			}
		},
		downloadFile(url) {
			// A simple way to trigger a download
			window.location.href = url
		},
	},
}
</script>

<style lang="scss" scoped>
/* Scoped styles ensure they only apply to this component */
.project-widget {
	padding: 16px;
}

.loading-placeholder {
	display: flex;
	justify-content: center;
	align-items: center;
	min-height: 150px;
    padding-top: 5px;
}

.project-card {
	margin-bottom: 16px;
}

.project-header {
	display: flex;
	align-items: center;
	gap: 8px; /* Creates space between the icon and the title */

	h3 {
		margin: 0;
		font-size: 16px;
		font-weight: bold;
	}
}

.file-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.file-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 4px;
	border-bottom: 1px solid var(--color-border);

	&:last-child {
		border-bottom: none;
	}

	.file-name {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
}

.empty-files {
	padding: 8px 4px;
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.project-widget {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 16px;
}

.project-list {
    flex-grow: 1;
    overflow-y: auto;
    min-height: 0;
}
</style>