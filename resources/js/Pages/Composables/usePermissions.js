import { usePage } from "@inertiajs/vue3";

export default function usePermissions() {
    const page = usePage();
    const permissions = page.props?.auth?.permissions || {};

    const canView = (roleId) => {
        if (roleId === "AboutUs") {
            return true;
        } else {
            return !!permissions?.[roleId]?.can_view;
        }
    };

    const canUpdate = (roleId) => {
        return !!permissions?.[roleId]?.can_update;
    };

    const canInsert = (roleId) => {
        return !!permissions?.[roleId]?.can_insert;
    };

    const canDelete = (roleId) => {
        return !!permissions?.[roleId]?.can_delete;
    };

    const canPrint = (roleId) => {
        return !!permissions?.[roleId]?.can_print;
    };

    const canTag = (roleId) => {
        return !!permissions?.[roleId]?.can_tag;
    };

    const canReprint = (roleId) => {
        return !!permissions?.[roleId]?.can_reprint;
    };

    const aboutUs = (roleId) => {};

    return {
        canView,
        canUpdate,
        canInsert,
        canDelete,
        canPrint,
        canTag,
        canReprint,
    };
}
