// Composables/useCollection.js
import { ref, onMounted } from "vue";
import axios from "axios";

export function useDepartment(options = {}) {
    const departments = ref([]); // Pastikan ini ref
    const loading = ref(false);
    const error = ref(null);

    const fetchDepartments = async () => {
        loading.value = true;
        try {
            const response = await axios.get("/departments/options");
            departments.value = response.data; // Set value, bukan departments
            console.log("Departments fetched:", departments.value); // Debug
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
        departments, // Return ref
        loading,
        error,
        fetchDepartments,
    };
}
