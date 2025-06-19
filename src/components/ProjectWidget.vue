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
			<NcNoteCard v-for="project in filteredProjects" :key="project.name" class="project-card">
				<template #header>
					<div class="project-header">
						<Folder :size="20" class="icon" />
						<h3>{{ project.name }}</h3>
					</div>
				</template>
				<ul class="file-list">
					<li v-for="file in project.files" :key="file.name" class="file-item">
						<span class="file-name">{{ file.name }}</span>
						<NcActions>
							<NcActionButton
								icon="icon-download"
								:title="t('your_app_name', 'Download')"
								@click="downloadFile(file.download_url)" />
						</NcActions>
					</li>
					<li v-if="project.files.length === 0" class="empty-files">
						{{ t('your_app_name', 'This project is empty') }}
					</li>
				</ul>
			</NcNoteCard>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import { generateOcsUrl } from '@nextcloud/router'
import { NcActions, NcActionButton, NcEmptyContent, NcNoteCard, NcTextField } from '@nextcloud/vue'
import { t } from '@nextcloud/l10n'

import Folder from 'vue-material-design-icons/Folder.vue'
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import NcIconSvgWrapper from '@nextcloud/vue/components/NcIconSvgWrapper'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import folderSvg from '@mdi/svg/svg/folder.svg?raw'

export default {
	name: 'ProjectWidget',
	components: {
		NcActions,
		NcActionButton,
		NcEmptyContent,
		NcNoteCard,
		NcLoadingIcon,
		NcTextField,
		Folder,
		FolderOutline,
        NcIconSvgWrapper,
        Magnify,
        folderSvg
	},
	data() {
		return {
			projects: [],
			loading: true,
			searchQuery: '',
			t,
            folderSvg
		}
	},
	computed: {
		filteredProjects() {
			if (!this.searchQuery) {
				return this.projects
			}
			return this.projects.filter(project => {
				return project.name.toLowerCase().includes(this.searchQuery.toLowerCase())
			})
		},
	},
	async mounted() {
		await this.fetchProjects()
	},
	methods: {
		async fetchProjects() {
			this.loading = true
			// try {
			// 	// Remember to replace 'your_app_name' with the actual ID of your app
			// 	const url = generateOcsUrl('/apps/your_app_name/api/v1/projects')
			// 	const response = await axios.get(url)
			// 	this.projects = response.data.ocs.data
			// } catch (e) {
			// 	console.error('Failed to fetch projects:', e)
			// 	// You could use NcToast to show an error message
			// } finally {
			// 	this.loading = false
			// }
		},
		searchProjects() {
			// The computed property 'filteredProjects' handles the filtering reactively
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
</style>