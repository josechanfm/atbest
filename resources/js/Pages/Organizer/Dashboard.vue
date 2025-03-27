<template>
    <OrganizerLayout :title="$t('organization_management')">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Management Dashboard

            </h2>
        </template>
        <div class="container mx-auto">
            <div class="flex flex-col-reverse md:flex-row gap-6">
                <div class="flex-auto">
                    <div class="container mx-auto mt-5 bg-white rounded-lg p-2 h-full">
                        <inertia-link v-if="member" :href="route('member.dashboard')" class="ant-btn">{{ $t('member_dashboard') }}</inertia-link>
                    </div>
                </div>
                <div class="flex-none ">
                    <div class="container mx-auto pt-5">
                        <div class="bg-white relative shadow rounded-lg">
                            <div class="w-full">
                                <h3 class="font-medium text-gray-900 text-left px-6">
                                    {{ $t('my_organizations') }}
                                    <inertia-link :href="route('organizer.organizations.index')" class="float-right">{{ $t('organization_list') }}</inertia-link>
                                </h3>
                                <div class="mt-5 w-full flex flex-col items-center overflow-hidden text-sm">
                                    <template v-for="org in organizations">
                                        <a href="#"
                                            class="w-full border-t border-gray-100 text-gray-600 py-4 pl-6 pr-3 w-full block hover:bg-gray-100 transition duration-150">
                                            <img src="https://avatars0.githubusercontent.com/u/35900628?v=4" alt=""
                                                class="rounded-full h-6 shadow-md inline-block mr-2" />
                                            {{ org.abbr }} - {{ org['name_' + $t('lang')] }}
                                            <span class="text-gray-500 text-xs float-right">
                                                <inertia-link
                                                    :href="route('organizer.organizations.edit', org.id)">{{ $t('edit')
                                                    }}</inertia-link>
                                            </span>
                                            <span class="text-gray-500 text-xs float-right pr-2"
                                                v-if="organizations.length > 1">
                                                <a @click="switchOrganization(org)">{{ $t('organization_switch') }}</a>
                                            </span>
                                        </a>
                                    </template>
                                </div>
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
import OrganizationList from '@/Components/Organization/OrganizationList.vue';

export default {
    components: {
        OrganizerLayout,
        OrganizationList,
    },
    props: ['organizations', 'member'],
    data() {
        return {

        }
    },
    methods: {
        switchOrganization(organization) {
            this.$inertia.post(route('organizer.organization.switch', { organization: organization.id }))
        }

    },
}
</script>