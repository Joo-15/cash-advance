// composables/useDepartment.js
import { onMounted, ref } from "vue";
import axios from "axios";

export function useDepartment() {
    const departments = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const fetchDepartments = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get("/departments/options");
            departments.value = response.data;
            // console.log("response options", response.data);
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                err.message ||
                "Gagal mengambil data department";
            console.error("Error fetching departments:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getDepartmentById = (id) => {
        return departments.value.find((dept) => dept.value === id);
    };

    const getDepartmentLabel = (id) => {
        const department = getDepartmentById(id);
        return department ? department.label : "";
    };

    const reset = () => {
        departments.value = [];
        loading.value = false;
        error.value = null;
    };

    onMounted(() => {
        fetchDepartments();
    });

    return {
        departments,
        loading,
        error,
        fetchDepartments,
        getDepartmentById,
        getDepartmentLabel,
        reset,
    };
}
