import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';

const axiosClient = axios.create({
    baseURL: baseURL,
    withCredentials: true,
    withXSRFToken: true,
})

axiosClient.interceptors.request.use((req) => {
    req.headers['X-Requested-With'] = 'XMLHttpRequest'
    req.headers['Accept'] = 'application/json'
    req.headers['Content-Type'] = 'application/json'
    return req;
})

axiosClient.interceptors.response.use(
    (response) => {
        return response
    },
    (error) => {
        throw error;
    },
)

export default axiosClient
