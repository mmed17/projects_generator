<script>
import ProjectCreator from './components/ProjectCreator.vue';
import NcAppContent from '@nextcloud/vue/components/NcAppContent';

export default {
	name: 'App',
	components: {
		NcAppContent,
		ProjectCreator
	},
	data() {},
	computed: {},
	methods: {}
}
</script>


<template>
	<NcAppContent>
		<ProjectCreator></ProjectCreator>
	</NcAppContent>
</template>