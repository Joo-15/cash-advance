import { computed, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useMessage } from "naive-ui";
import { sleep } from "@/utils/helpers";

export function useEditCashAdvance({
    props,
    loadingEdit,
    emits,
    routeName,
    formRef,
}) {
    /* ========================
     | State
     ======================== */
    const form = ref({});
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
                form.value = {
                    ...val,
                    tanggal: val.tanggal
                        ? new Date(val.tanggal).getTime()
                        : null,
                };
            }
        },
        { immediate: true, deep: true }
    );

    watch(
        () => props.dataEdit,
        (val) => {
            if (val) {
                Object.assign(form, {
                    ...val,
                    tanggal: val.tanggal ? new Date(val.tanggal).getTime() : null
                })
            }
        },
        { immediate: true }
    )

    watch(
        () => props.show,
        async (val) => {
            if (val) {
                loadingEdit.value = true;

                await sleep(500);
                loadingEdit.value = false;
            }
        }
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
        submit,
    };
}
