import axios from "axios";

export class UsersSerice {

    instance = null;

    constructor() {}

    /**
     * @returns {UsersSerice}
     */
    static getInstance() {
        if(this.instance) {
            return this.instance;
        }

        return new UsersSerice();
    }

    /**
     * Search users
     * @param {string} query 
     * @returns {Promise<any[]>}
     */
    async search(query) {
        if (!query.trim()) {
            return [];
        }

        try {
            const response = await axios.get('/ocs/v1.php/cloud/users', {
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
            const users = [];

            for( let i = 0; i < userIds.length; i++) {
                const userId  = userIds[i];
                const details = await this.fetchDetails(userId);
                
                if(details) {
                    users.push({
                        id: details.id,
                        user: details.id,
                        label: details.displayName,
                        displayName: details.displayName,
                        subname: details.email,
                    });
                }
            }

            return users;

        } catch (error) {
            console.error('Error fetching users:', error);
            return [];
        }
    }

    /**
     * 
     * @param {string} userId 
     */
    async fetchDetails(userId) {
        try {
            const response = await axios.get(`/ocs/v1.php/cloud/users/${userId}`, {
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
    }
}