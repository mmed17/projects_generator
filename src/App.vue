<template>
	<NcAppContent>
		<div id="projectcreatoraio" class="project-creator-container">
			<div class="project-creator-form">
				<h1 class="project-creator-title">Create a New Project</h1>
				<p class="project-creator-subtitle">
					Fill out the details below to set up your new project environment.
				</p>

				<!-- Submission Status Message -->
				<NcNoteCard v-if="submissionStatus" :type="submissionStatus">
					{{ statusMessage }}
				</NcNoteCard>

				<form>
					<!-- Project Name -->
					<div class="form-row">
						<NcTextField v-model="project.name"
							label="Project Name*"
							class="form-row-item"
							placeholder="e.g., Q4 Marketing Campaign"
							:show-label="true"
							input-label="Project Name"/>

						<NcTextField v-model="project.number"
							label="Project Number*"
							placeholder="e.g., P-2025-001"
							:show-label="true"
							input-label="Project Number"
							class="form-row-item"/>
					</div>

					<!-- Project description -->
					<div class="form-row">
						<NcTextArea
							v-model="project.description"
							class="form-row-item"
							label="Project description"
							placeholder="Provide some details"
							:show-label="true"
							input-label="Project Description"
							rows="4"/>
					</div>

					<!-- Project Address -->
					<div class="form-row">
						<NcTextField v-model="project.address"
							class="form-row-item"
							label="Client Address or Location"
							placeholder="e.g., 123 Innovation Drive, Tech City"
							:show-label="true"
							input-label="Client Address or Location"/>
					</div>

					<!-- Project Number & Type (in a row) -->
					<div class="form-row">
						<NcSelect v-model="selectedProjectType"
							class="form-row-item"
							placeholder="Select project type"
							input-label="Project Type*"
							:options="PROJECT_TYPES"
							:show-label="true"
							:multiple="false"
							/>
					</div>

					<!-- Project Members -->
					<div class="form-row">
						<UsersFetcher 
							class="form-row-item"
							input-label="Project Team Members*"
							placeholder="Select team members"
							:model-value="project.members"
							@update:modelValue="project.members = $event">
						</UsersFetcher>
					</div>
					<!-- Action Button -->
					<NcButton 
							:disabled="isCreatingProject || !project.name || !project.number || isNaN(project.type) || project.members.length === 0"
							type="primary"
							:wide="true"
							@click="createProject"
							class="submit-button">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ isCreatingProject ? 'Creating Project...' : 'Create Project' }}
					</NcButton>
				</form>
			</div>
		</div>
	</NcAppContent>
</template>

<script>
import {
	NcAppContent,
	NcButton,
	NcTextField,
	NcSelect,
	NcNoteCard
} from '@nextcloud/vue';
import { PROJECT_TYPES } from './macros/project-types';
import { ProjectsService } from './Services/projects';
import { Project } from './Models/project';

import UsersFetcher from './components/UsersFetcher.vue';
import NcTextArea from '@nextcloud/vue/components/NcTextArea';
import Plus from 'vue-material-design-icons/Plus.vue';

const projectsService = ProjectsService.getInstance();

export default {
	name: 'App',
	components: {
		NcAppContent,
		NcButton,
		NcTextField,
		NcSelect,
		NcNoteCard,
		UsersFetcher,
		NcTextArea,
		Plus,
	},
	data() {
		return {
			project: new Project(),
			isCreatingProject: false,
			submissionStatus: '',
			statusMessage: '',
			PROJECT_TYPES
		};
	},
	computed: {
		selectedProjectType: {
			get() {
				return this.PROJECT_TYPES.find((type) => type.id === this.project.type) || null;
			},
			set(option) {
				this.project.type = option ? option.id : null;
			}
		}
	},
	methods: {
		async createProject() {
			this.isCreatingProject = true;
			this.submissionStatus = '';
			this.statusMessage = '';

			try {
				await projectsService.create(this.project);
				this.showProjectCreationSuccessMessage();
				this.resetProjectForm();
				
				setTimeout(() => {
					this.resetProjectCreationMessage();
				}, 4000);

			} catch (error) {
				this.showProjectCreationErrorMessage(error);
				console.error('Error creating project:', error);
			} finally {
				this.isCreatingProject = false;
			}
		},
		resetProjectForm() {
			this.project = new Project();
		},
		showProjectCreationSuccessMessage() {
			this.submissionStatus = 'success';
			this.statusMessage = 'Project has been created successfully';
		},
		/**
		 * @param error {Error}
		 */
		showProjectCreationErrorMessage(error) {
			this.submissionStatus = 'error';
			this.statusMessage = error.message || 'An unknown error occurred.';
		},
		resetProjectCreationMessage() {
			this.submissionStatus = '';
			this.statusMessage = '';
		}
	}
}
</script>

<style scoped>
.project-creator-container {
	padding: 48px;
	display: flex;
	justify-content: center;
	width: 100%;
}

.project-creator-form {
	max-width: 700px;
	width: 100%;
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.project-creator-title {
	font-size: 2em;
	font-weight: bold;
	color: var(--color-main-text);
	margin-bottom: 0;
}

.project-creator-subtitle {
	font-size: 1.1em;
	color: var(--color-text-maxcontrast);
	margin-top: -16px;
	margin-bottom: 16px;
}

.form-row {
	display: flex;
	gap: 24px;
	align-items: space-between;
	justify-content: center;
	margin: 8px 0px;
}

.form-row-item {
	flex: 1;
}

.submit-button {
	margin-top: 16px;
	height: 44px; 
	font-size: 1.1em;
}

.status-card {
	margin-bottom: -8px;
}
</style>