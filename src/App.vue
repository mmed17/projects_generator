<template>
	<NcAppContent>
		<div id="projectcreatoraio" class="project-creator-container">
			<div class="project-creator-form">
				<h1 class="project-creator-title">Create a New Project</h1>
				<p class="project-creator-subtitle">
					Fill out the details below to set up your new project environment.
				</p>

				<!-- Submission Status Message -->
				<NcNoteCard v-if="submissionStatus"
					:type="submissionStatus === 'success' ? 'success' : 'error'"
					class="status-card">
						{{ statusMessage }}
				</NcNoteCard>

				<form @submit.prevent="createProject">
					<!-- Project Name -->
					<div class="form-row">
						<NcTextField v-model="projectName"
							label="Project Name"
							class="form-row-item"
							placeholder="e.g., Q4 Marketing Campaign"
							:show-label="true"
							required />

						<NcTextField v-model="projectNumber"
							label="Project Number"
							placeholder="e.g., P-2025-001"
							:show-label="true"
							class="form-row-item"
							required />
					</div>

					<!-- Project Number & Type (in a row) -->
					<div class="form-row">
						<NcSelect
							v-model="projectType"
							class="form-row-item"
							label="Project Type"
							placeholder="Select project type"
							option-value="value"
							option-label="label"
							:options="projectTypeOptions"
							:no-wrap="false"
							:multiple="false"
							required />
					</div>

					<!-- Project Address -->
					<div class="form-row">
						<NcTextField v-model="projectAddress"
							class="form-row-item"
							label="Client Address or Location"
							placeholder="e.g., 123 Innovation Drive, Tech City"
							:show-label="true" 
							required />
					</div>

					<!-- Project Members -->
					<div class="form-row">
						<NcSelect
							v-model="selectedMembers"
							class="form-row-item"
							label="Project Members"
							:options="userOptions"
							:no-wrap="false"
							placeholder="Select team members..."
							required
							multiple />
					</div>

					<!-- Action Button -->
					<NcButton :disabled="isLoading"
							type="primary"
							:wide="true"
							@click="createProject"
							class="submit-button">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ isLoading ? 'Creating Project...' : 'Create Project' }}
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
	NcNoteCard,

} from '@nextcloud/vue'
import { onBeforeMount } from 'vue';
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'App',
	components: {
		NcAppContent,
		NcButton,
		NcTextField,
		NcSelect,
		NcNoteCard,
		Plus,
	},
	data() {
		return {
			// Form field models
			projectName: '',
			projectNumber: '',
			projectAddress: '',
			projectType: '',
			selectedMembers: [],
			projectDescription: '',

			// Form status management
			isLoading: false,
			submissionStatus: null, // Can be 'success' or 'error'
			statusMessage: '',

			// Placeholder Data (Replace with real data from Nextcloud API)
			projectTypeOptions: [],
			userOptions: [],
		};
	},
	onBeforeMount() {
		// This is where you would typically fetch user data from the Nextcloud API
		// For this example, we're using static data defined in userOptions
		console.log('--- Fetching User Data ---');
		console.log('Available Users:', this.userOptions);
	},
	methods: {
		async createProject() {
			// In a real application, this is where you make your series of HTTP calls
			// to create the Team, Folder, Deck Board, etc.
			console.log('--- Submitting Project Data ---')
			console.log('Name:', this.projectName)
			console.log('Number:', this.projectNumber)
			console.log('Address:', this.projectAddress)
			console.log('Type:', this.projectType)
			console.log('Description:', this.projectDescription)
			console.log('Start Date:', this.startDate)
			console.log('End Date:', this.endDate)
			console.log('Members:', this.selectedMembers.map(m => m.value)) // Get just the user IDs

			// Simple validation check
			if (!this.projectName || this.selectedMembers.length === 0) {
				this.submissionStatus = 'error'
				this.statusMessage = 'Project Name and Members are required.'
				return
			}

			this.isLoading = true
			this.submissionStatus = null

			// Simulate network request
			await new Promise(resolve => setTimeout(resolve, 2000))

			// Simulate a successful response
			this.isLoading = false
			this.submissionStatus = 'success'
			this.statusMessage = `Project "${this.projectName}" has been created successfully!`
		},
	},
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
	gap: 24px; /* Space between form elements */
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
	margin-top: -16px; /* Bring subtitle closer to title */
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
	margin: 0px !important; /* Reset margin for form items */
}

.submit-button {
	margin-top: 16px;
	height: 44px; /* Taller button for emphasis */
	font-size: 1.1em;
}

.status-card {
	margin-bottom: -8px; /* Reduce gap if a card is shown */
}
</style>
