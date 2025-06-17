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

					<!-- Project description -->
					<div class="form-row">
						<NcTextArea
							v-model="projectDescription"
							class="form-row-item"
							label="Project description"
							placeholder="Provide some details"
							required
							rows="4"
						/>
					</div>

					<!-- Project Number & Type (in a row) -->
					<div class="form-row">
						<NcSelect v-model="projectType"
							class="form-row-item"
							placeholder="Select project type"
							input-label="Project Type"
							:options="types"
							:show-label="true"
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
						<NcSelectUsers :options="users"
							class="form-row-item"
							:model-value="selectedUsers"
							:multiple="true"
							:keep-open="true"
							:show-label="true"
							:no-wrap="true"
							input-label="Project Team Members"
							placeholder="Select team members"
							@search="fetchUsers"
							@update:modelValue="selectedUsers = $event" />
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
	NcNoteCard
} from '@nextcloud/vue'
import NcSelectUsers from '@nextcloud/vue/components/NcSelectUsers'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import Plus from 'vue-material-design-icons/Plus.vue'
import svgAccountGroup from '@mdi/svg/svg/account-group.svg?raw'
import svgEmail from '@mdi/svg/svg/email.svg?raw'

export default {
	name: 'App',
	components: {
		NcAppContent,
		NcButton,
		NcTextField,
		NcSelect,
		NcNoteCard,
		Plus,
		NcSelectUsers,
		svgAccountGroup,
		svgEmail,
		NcTextArea
	},
	data() {
		return {
			projectName: '',
			projectNumber: '',
			projectAddress: '',
			projectType: '',
			selectedUsers: [],
			projectDescription: '',

			isLoading: false,
			statusMessage: '',
			submissionStatus: null,

			users: [
				{
					uid: '0',
					id: '0-john',
					displayName: 'John',
					isNoUser: false,
					subname: 'john@example.org',
				},
				{
					uid: '1',
					id: '2-org@example.org',
					displayName: 'Organization',
					isNoUser: true,
					subname: 'org@example.org',
					iconSvg: svgEmail,
					iconName: 'Email icon'
				}
			],
			types: [
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
			]
		};
	},
	methods: {
		async fetchUsers(query) {
			console.log('Fetching users with query:', query);
		},
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
