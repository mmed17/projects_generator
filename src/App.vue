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

				<form @submit.prevent="createProject">
					<!-- Project Name -->
					<div class="form-row">
						<NcTextField v-model="projectName"
							label="Project Name*"
							class="form-row-item"
							placeholder="e.g., Q4 Marketing Campaign"
							:show-label="true"
							input-label="Project Name"
							/>

						<NcTextField v-model="projectNumber"
							label="Project Number*"
							placeholder="e.g., P-2025-001"
							:show-label="true"
							input-label="Project Number"
							class="form-row-item"
							/>
					</div>

					<!-- Project description -->
					<div class="form-row">
						<NcTextArea
							v-model="projectDescription"
							class="form-row-item"
							label="Project description"
							placeholder="Provide some details"
							:show-label="true"
							input-label="Project Description"
							rows="4"
						/>
					</div>

					<!-- Project Address -->
					<div class="form-row">
						<NcTextField v-model="projectAddress"
							class="form-row-item"
							label="Client Address or Location"
							placeholder="e.g., 123 Innovation Drive, Tech City"
							:show-label="true"
							input-label="Client Address or Location"
							/>
					</div>
					
					<!-- Project Number & Type (in a row) -->
					<div class="form-row">
						<NcSelect v-model="projectType"
							class="form-row-item"
							placeholder="Select project type"
							input-label="Project Type*"
							:options="types"
							:show-label="true"
							:multiple="false"
							/>
					</div>

					<!-- Project Members -->
					<div class="form-row">
						<NcSelectUsers :options="users"
							class="form-row-item"
							:model-value="projectMembers"
							:multiple="true"
							:keep-open="true"
							:show-label="true"
							:no-wrap="true"
							input-label="Project Team Members*"
							placeholder="Select team members"
							@search="fetchUsers"
							@update:modelValue="projectMembers = $event" 
						/>
					</div>

					<!-- Action Button -->
					<NcButton 
							:disabled="isCreatingProject || !projectName || !projectNumber || !projectType || projectMembers.length === 0"
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
} from '@nextcloud/vue'
import NcSelectUsers from '@nextcloud/vue/components/NcSelectUsers'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import Plus from 'vue-material-design-icons/Plus.vue'
import svgAccountGroup from '@mdi/svg/svg/account-group.svg?raw'
import svgEmail from '@mdi/svg/svg/email.svg?raw'
import { generateUrl } from '@nextcloud/router'

let searchTimeout = null;

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
			projectMembers: [],
			projectDescription: '',

			isFetchingUsers: false,
			isCreatingProject: false,
			submissionStatus: '',
			statusMessage: '',

			users: [],
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
			if (searchTimeout) {
				clearTimeout(searchTimeout);
			}

			this.users = [];
			
			if (!query.trim()) {
				return;
			}

			this.isFetchingUsers = true;
			searchTimeout = setTimeout(async () => {
				try {
					const response = await this.$axios.get('/ocs/v1.php/cloud/users', {
						params: {
							search: query,
							limit: 20
						},
						headers: { 
							'OCS-APIRequest': 'true',
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					});

					const userIds = response.data.ocs.data.users;
					const fullUsers = [];
					for( let i = 0; i < userIds.length; i++) {
						const userId = userIds[i];
						const userDetails = await this.getUserDetails(userId);
						if(!userDetails) continue;
						
						fullUsers.push({
							id: userDetails.id,
							user: userDetails.id,
							displayName: userDetails.displayName,
							subname: userDetails.email
						});
					}

					this.users = fullUsers;

				} catch (error) {
					console.error('Error fetching users:', error);
				} finally {
					this.isFetchingUsers = false;
				}
				
			}, 300);
		},
		async getUserDetails(userId) {
            try {
                const response = await this.$axios.get(`/ocs/v1.php/cloud/users/${userId}`, {
					headers: {
						'OCS-APIRequest': 'true',
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				});

				const details = response.data.ocs.data;
				return {
					id: details.id,
					displayName: details.displayname,
					email: details.email ?? details.phone,
					status: details.status
				};
            } catch (error) {
                console.error(`Error getting details for user ${userId}:`, error);
            }
        },
		async createProject() {
			this.isCreatingProject = true;
			this.isMessageVisible = false;
			this.submissionStatus = '';
			this.statusMessage = '';

			const projectData = {
				name: this.projectName.trim(),
				number: this.projectNumber.trim(),
				description: this.projectDescription.trim(),
				type: this.projectType.id,
				address: this.projectAddress.trim(),
				members: this.projectMembers.map(m => m.id)
			};

			try {
				const url = generateUrl('/apps/projectcreatoraio/api/v1/projects');
				const response = await this.$axios.post(url, projectData, {
					headers: {
						'OCS-APIRequest': 'true',
						'Content-Type': 'application/json'
					}
				});

				this.submissionStatus = 'success';
				this.statusMessage = response.data.message || 'Project created successfully!';
				this.resetForm();
				
				setTimeout(() => {
					this.submissionStatus = '';
				}, 4000);

			} catch (error) {
				this.submissionStatus = 'error';
				this.statusMessage = error.message || 'An unknown error occurred.';
				console.error('Error creating project:', error);

			} finally {
				this.isCreatingProject = false;
			}
		},
		resetForm() {
			this.projectName = '';
			this.projectNumber = '';
			this.projectAddress = '';
			this.projectType = '';
			this.projectMembers = [];
			this.projectDescription = '';
			this.users = [];
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
