// Composables/useCollection.js
import { ref, onBeforeMount, onMounted } from "vue"; // ← Ganti onMounted → onBeforeMount
import axios from "axios";

export function useDepartment(options = {}) {
    const departments = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const fetchDepartments = async () => {
        loading.value = true;
        try {
            const response = await axios.get("/departments/options");
            departments.value = response.data;

            return response.data;
        } catch (err) {
            error.value = err;
            console.error("Error fetching departments:", err);
        } finally {
            loading.value = false;
        }
    };

    onMounted(() => {
        fetchDepartments();
    });

    return {
        departments,
        loading,
        error,
        fetchDepartments,
    };
}