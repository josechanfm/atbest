<template>
    <OrganizerLayout :title="$t('organization_management')">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t('management_dashboard') }}
                </h2>
                <inertia-link 
                    :href="route('organizer.organizations.index')" 
                    class="text-sm text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-4 py-2 rounded-lg transition">
                    {{ $t('manage_all_organizations') }}
                </inertia-link>
            </div>
        </template>

        <div class="mx-auto">
            <div class="flex flex-col-reverse lg:flex-row gap-8">
                <!-- Main Content Area -->
                <div class="flex-auto">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 h-full">
                        <div class="text-center lg:text-left">
                            <!-- Welcome Icon -->
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-indigo-500 mx-auto lg:mx-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                {{ $t('welcome_back') }}, {{ $page.props.auth.user.name }}
                            </h3>
                            <p class="text-gray-600 text-lg leading-relaxed">
                                {{ $page.props.auth.organization.abbr }}
                                {{ $page.props.auth.organization.name_zh }}
                            </p>
                            
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-blue-800">
                                        {{ $t('organization_management') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Organization List (Redesigned) -->
                <div class="flex lg:w-[380px]">
                    <div class="w-full">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-semibold text-white text-lg">
                                            {{ $t('my_organizations') }}
                                        </h3>
                                        <p class="text-indigo-200 text-xs mt-1">
                                            {{ $t('you_have_organizations', { count: myOrganizations.length }) }}
                                        </p>
                                    </div>
                                    <svg class="w-6 h-6 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Organization List -->
                            <div class="divide-y divide-gray-100">
                                <template v-for="member in myOrganizations">
                                    <div 
                                        v-if="member.is_organizer" 
                                        :class="[
                                            'group relative transition-all duration-200',
                                            currentOrganizationId === member.organization_id ? 'bg-indigo-50' : 'hover:bg-gray-50'
                                        ]"
                                    >
                                        <div class="px-6 py-4">
                                            <!-- Organization Header -->
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <!-- Avatar -->
                                                    <div class="flex-shrink-0">
                                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center">
                                                            <span class="text-indigo-700 font-semibold text-sm">
                                                                {{ getInitials(member.organization.abbr) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Organization Info -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-semibold text-gray-900 text-sm">
                                                                {{ member.organization.abbr }}
                                                            </span>
                                                            <span v-if="currentOrganizationId === member.organization_id" 
                                                                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                {{ $t('selected') }}
                                                            </span>
                                                        </div>
                                                        <div class="text-gray-600 text-xs truncate mt-0.5">
                                                            {{ formatOrganizationName(member) }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Active Indicator -->
                                                <div v-if="currentOrganizationId === member.organization_id" 
                                                     class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse flex-shrink-0 mt-2">
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center justify-end gap-3 mt-3 pt-2 border-t border-gray-100">
                                                <button 
                                                    @click="switchOrganization(member.organization)"
                                                    :disabled="currentOrganizationId === member.organization_id"
                                                    :class="[
                                                        'text-xs px-3 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1',
                                                        currentOrganizationId === member.organization_id 
                                                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                                                            : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 hover:text-indigo-900'
                                                    ]"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                    </svg>
                                                    {{ currentOrganizationId === member.organization_id ? $t('active') : $t('switch') }}
                                                </button>
                                                
                                                <inertia-link 
                                                    :href="route('organizer.organizations.edit', member.organization_id)"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-all duration-150 flex items-center gap-1"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                    </svg>
                                                    {{ $t('edit') }}
                                                </inertia-link>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State -->
                                <div v-if="!myOrganizations.length" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <p class="text-gray-500 text-sm">{{ $t('no_organizations_found') }}</p>
                                </div>
                            </div>

                            <!-- Footer Link -->
                            <div class="border-t border-gray-200 bg-gray-50 px-6 py-3">
                                <inertia-link 
                                    :href="route('organizer.organizations.index')" 
                                    class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ $t('view_all_organizations') }}
                                </inertia-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizerLayout>
</template>

<script>
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue';

export default {
    components: {
        OrganizerLayout,
    },
    props: {
        organizations: Array,
        members: Array,
    },
    computed: {
        myOrganizations() {
            return this.members?.filter(member => member.is_organizer) || [];
        },
        currentOrganizationId() {
            return this.$page.props.auth.organization?.id;
        }
    },
    methods: {
        switchOrganization(organization) {
            if (this.currentOrganizationId === organization.id) return;
            
            this.$inertia.post(route('organizer.organization.switch', { 
                organization: organization.id 
            }), {}, {
                preserveScroll: true,
                preserveState: false,
            });
        },
        
        formatOrganizationName(member) {
            const lang = this.$t('lang');
            const name = member.organization[`name_${lang}`];
            const maxLength = 40;
            
            if (!name) return '';
            
            if (name.length <= maxLength) {
                return name;
            }
            
            return '...' + name.slice(-maxLength + 3);
        },
        
        getInitials(abbr) {
            return abbr?.substring(0, 2).toUpperCase() || 'ORG';
        }
    }
}
</script>

<style scoped>
/* Smooth transitions */
.group {
    transition: all 0.2s ease;
}

/* Custom scrollbar for organization list if needed */
.overflow-y-auto {
    scrollbar-width: thin;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>