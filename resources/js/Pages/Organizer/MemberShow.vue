<template>
  <OrganizerLayout :title="$t('view_member_profile')" :breadcrumb="breadcrumb">
    <div class="mx-auto max-w-5xl space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="m-0 text-2xl font-semibold text-slate-900">{{ $t('view_member_profile') }}</h1>
        </div>
        <inertia-link
          :href="route('organizer.members.index')"
          class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:border-red-300 hover:text-red-600"
        >
          <arrow-left-outlined />
          {{ $t('back') }}
        </inertia-link>
      </div>

      <template v-if="member">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
          <div class="h-20 bg-gradient-to-r from-red-700 via-red-600 to-orange-400"></div>
          <div class="flex flex-col gap-4 px-6 pb-6 sm:flex-row sm:items-end sm:gap-6">
            <div class="-mt-10 flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border-4 border-white bg-slate-100 text-4xl text-slate-400 shadow-sm">
              <img
                v-if="avatarSrc && !avatarFailed"
                :src="avatarSrc"
                :alt="$t('avatar')"
                class="h-full w-full object-cover"
                @error="avatarFailed = true"
              />
              <user-outlined v-else aria-hidden="true" />
            </div>
            <div class="min-w-0 flex-1">
              <h2 class="m-0 break-words text-xl font-semibold text-slate-900">{{ displayValue(member.display_name) }}</h2>
              <p class="mb-0 mt-1 text-sm text-slate-500">{{ displayValue(organizationName) }}</p>
            </div>
            <div class="sm:text-right">
              <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $t('member_number') }}</div>
              <div class="mt-1 font-mono text-sm font-semibold text-slate-800">{{ displayValue(member.number) }}</div>
            </div>
          </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
          <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-slate-900">{{ $t('basic_information') }}</h2>
            <dl class="m-0 divide-y divide-slate-100">
              <div v-for="field in personalFields" :key="field.key" class="grid grid-cols-2 gap-3 py-3 text-sm">
                <dt class="text-slate-500">{{ $t(field.key) }}</dt>
                <dd class="m-0 break-words font-medium text-slate-800">{{ displayValue(field.value) }}</dd>
              </div>
            </dl>
          </section>

          <div class="space-y-5">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 class="mb-4 text-base font-semibold text-slate-900">{{ $t('contact_information') }}</h2>
              <dl class="m-0 divide-y divide-slate-100">
                <div v-for="field in contactFields" :key="field.key" class="grid grid-cols-2 gap-3 py-3 text-sm">
                  <dt class="text-slate-500">{{ $t(field.key) }}</dt>
                  <dd class="m-0 break-words font-medium text-slate-800">{{ displayValue(field.value) }}</dd>
                </div>
              </dl>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 class="mb-4 text-base font-semibold text-slate-900">{{ $t('membership') }}</h2>
              <dl class="m-0 divide-y divide-slate-100">
                <div v-for="field in membershipFields" :key="field.key" class="grid grid-cols-2 gap-3 py-3 text-sm">
                  <dt class="text-slate-500">{{ $t(field.key) }}</dt>
                  <dd class="m-0 break-words font-medium text-slate-800">{{ displayValue(field.value) }}</dd>
                </div>
              </dl>
            </section>
          </div>
        </div>
      </template>
      <div v-else class="rounded-xl border border-slate-200 bg-white p-6 text-slate-500">
        {{ $t('not_provided') }}
      </div>
    </div>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { ArrowLeftOutlined, UserOutlined } from "@ant-design/icons-vue";

export default {
  components: { OrganizerLayout, ArrowLeftOutlined, UserOutlined },
  props: {
    member: { type: Object, default: null },
  },
  data() {
    return { avatarFailed: false };
  },
  computed: {
    breadcrumb() {
      return [
        { label: this.$t('members'), url: route('organizer.members.index') },
        { label: this.$t('view_member_profile'), url: null },
      ];
    },
    avatarSrc() {
      const avatar = this.member?.avatar;
      return avatar && /^(https?:)?\/\//i.test(avatar) ? avatar : this.member?.avatar_url;
    },
    organizationName() {
      const organization = this.member?.organization;
      return organization?.name_zh || organization?.name_en || organization?.name_pt || organization?.abbr_en;
    },
    personalFields() {
      const member = this.member || {};
      const gender = member.gender === 'M' ? this.$t('male') : member.gender === 'F' ? this.$t('female') : member.gender;
      return [
        { key: 'given_name', value: member.given_name },
        { key: 'middle_name', value: member.middle_name },
        { key: 'family_name', value: member.family_name },
        { key: 'gender', value: gender },
        { key: 'dob', value: member.dob },
        { key: 'nationality', value: member.nationality },
      ];
    },
    contactFields() {
      const member = this.member || {};
      return [
        { key: 'email', value: member.email },
        { key: 'mobile', value: member.mobile },
        { key: 'address', value: member.address },
        { key: 'city', value: member.city },
        { key: 'country', value: member.country },
      ];
    },
    membershipFields() {
      const member = this.member || {};
      return [
        { key: 'organization', value: this.organizationName },
        { key: 'valid_from', value: member.valid_at },
        { key: 'valid_until', value: member.expired_at },
        { key: 'is_organizer', value: this.booleanValue(member.is_organizer) },
        { key: 'is_dismissed', value: this.booleanValue(member.is_dismissed) },
      ];
    },
  },
  watch: {
    avatarSrc() {
      this.avatarFailed = false;
    },
  },
  methods: {
    displayValue(value) {
      return value === null || value === undefined || value === '' ? this.$t('not_provided') : value;
    },
    booleanValue(value) {
      return value === null || value === undefined ? null : this.$t(value ? 'yes' : 'no');
    },
  },
};
</script>
