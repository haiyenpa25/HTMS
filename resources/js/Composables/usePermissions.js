import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const isSuperAdmin = computed(() => {
        return page.props.auth?.user?.is_superadmin || false;
    });

    const allowedFeatures = computed(() => {
        return page.props.auth?.allowed_features || [];
    });

    const canDisplayFeature = (featureSlug) => {
        // For Dashboard rendering, we strictly rely on the backend's allowedFeatures array
        // because the backend already scoped it down to the CURRENT active department's block.
        // SuperAdmins shouldn't see 'attendance' inside Ministry unless Ministry is configured for it.
        return allowedFeatures.value.includes(featureSlug);
    };

    const can = (featureSlug) => {
        return canDisplayFeature(featureSlug);
    };

    return {
        can,
        isSuperAdmin,
        allowedFeatures
    };
}
