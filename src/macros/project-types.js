import { t } from '@nextcloud/l10n';
import { APP_ID } from './app-id.js';

export const PROJECT_TYPES = [
	{ id: 0, label: t(APP_ID, 'Combi') },
	{ id: 1, label: t(APP_ID, 'Solo Elektra ') },
	{ id: 2, label: t(APP_ID, 'Solo Water') },
	{ id: 3, label: t(APP_ID, 'Custom ') }
];