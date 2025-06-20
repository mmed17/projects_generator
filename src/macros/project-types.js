import { t } from '@nextcloud/l10n';
import { APP_ID } from './app-id.js';

export const PROJECT_TYPES = [
	{ id: 0, label: t(APP_ID, 'Marketing Campaign') },
	{ id: 1, label: t(APP_ID, 'Product Development') },
	{ id: 2, label: t(APP_ID, 'Research Project') },
	{ id: 3, label: t(APP_ID, 'Event Planning') },
	{ id: 4, label: t(APP_ID, 'Consulting Engagement') },
	{ id: 5, label: t(APP_ID, 'Training Program') },
	{ id: 6, label: t(APP_ID, 'Software Development') },
	{ id: 7, label: t(APP_ID, 'Infrastructure Upgrade') },
	{ id: 8, label: t(APP_ID, 'Community Outreach') },
	{ id: 9, label: t(APP_ID, 'Other') }
];