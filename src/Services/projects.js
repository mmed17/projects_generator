import { generateUrl } from "@nextcloud/router";
import { Project } from "../Models/project";
import axios from "axios";

export class ProjectsService {

    instance = null;
    constructor() {}

    /**
     * 
     * @returns {ProjectsService}
     */
    static getInstance() {
        if(this.instance) {
            return this.instance;
        }

        return new ProjectsService();
    }

    /**
     * 
     * @param {Project} project 
     * @returns {any}
     */
    async create(project) {
        const url = generateUrl('/apps/projectcreatoraio/api/v1/projects');
        const response = await axios.post(url, project.toJson(), {
            headers: {
                'OCS-APIRequest': 'true',
                'Content-Type': 'application/json'
            }
        });

        return response.data;
    }

    /**
     * 
     * @returns {Promise<any[]>}
     */
    async list() {
        try {
            const url = generateUrl('/apps/projectcreatoraio/api/v1/projects/list')
            const response = await axios.get(url, {
                headers: {
                    'OCS-APIRequest': 'true',
                    'Content-Type': 'application/json'
                }
            });

            return response.data ?? [];

        } catch (e) {
            console.error('Failed to fetch projects:', e);
            return [];
        }
    }

    /**
     * 
     * @param {string} userId 
     * 
     * @returns {Promise<any[]>}
     */
    async fetchProjectsByUser(userId) {
        try {
            const url = generateUrl(`/apps/projectcreatoraio/api/v1/users/${userId}/projects`);
            const response = await axios.get(url, {
                headers: {
                    'OCS-APIRequest': 'true',
                    'Content-Type': 'application/json'
                }
            });
            return response.data ?? [];
        } catch(e) {
            console.error('Failed to fetch user projects', e);
            return [];
        }
    }

    /**
     * get projects by name
     * @param {string} query
     */
    async search(query) {
        try {
            const url = generateUrl(`/apps/projectcreatoraio/api/v1/projects/search`);
            
            const params = new URLSearchParams();
            params.append('search', query);
    
            const response = await axios.get(`${url}?${params.toString()}`, {
                headers: {
                    'OCS-APIRequest': 'true',
                    'Content-Type': 'application/json'
                }
            });
    
            return response.data ?? [];

        } catch(e) {
            console.error("Failed to search projects", e);
            return [];
        }
    }
}