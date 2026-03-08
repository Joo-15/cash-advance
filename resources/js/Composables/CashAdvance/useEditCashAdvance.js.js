import { computed, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useMessage } from "naive-ui";

export function useEditCashAdvance({
    props,
    emits,
    routeName,
    formDefault = {},
    formRef,
}) {
    /* ========================
     | State
     ======================== */
    const form = ref({ ...formDefault });
    const loading = ref(false);
    const message = useMessage();
    const page = usePage();

    /* ========================
     | Modal Control
     ======================== */
    const showModal = computed({
        get: () => props.show,
        set: (val) => emits("update:show", val),
    });

    /* ========================
     | Sync Edit Data
     ======================== */
    watch(
        () => props.dataEdit,
        (val) => {
            if (val && Object.keys(val).length > 0) {
                form.value = { ...val };
            }
        },
        { immediate: true, deep: true }
    );

    /* ========================
    | Submit
    ======================== */
    const submit = () => {
        formRef.value.validate().then((valid) => {
            if (!valid) return;

            loading.value = true;
            const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

            router.put(
                route(routeName, form.value.id),
                form.value,
                {
                    preserveScroll: true,
                    onStart: () => {
                        loading.value = true;
                    },
                    onSuccess: async () => {
                        // pastikan minimal 300ms loading
                        await delay(300);
                        loading.value = false;
                        emits("update:show", false); // tutup modal
                        message.success(page.props.flash.success);
                    },
                    onError: async () => {
                        await delay(300);
                        loading.value = false;
                    },
                },
            );
        });
    };

    return {
        form,
        showModal,
        loading,
        form,
        submit,
    };
}
