<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Icon, { QuestionCircleOutlined } from "@ant-design/icons-vue";

defineProps({
  organizations: Object,
});

const form = useForm({
  name: "",
  given_name: "",
  family_name: "",
  registration_code: "",
  email: "",
  password: "",
  password_confirmation: "",
  terms: false,
});

const submit = () => {
  form.post(route("register"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <Head title="Register" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="name" :value="$t('name')" />
        <TextInput
          id="name"
          v-model="form.name"
          type="text"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="name"
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>
      <div class="mt-4">
        <InputLabel for="given_name" :value="$t('given_name')" />
        <TextInput
          id="given_name"
          v-model="form.given_name"
          type="text"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="given_name"
        />
        <InputError class="mt-2" :message="form.errors.given_name" />
      </div>
      <div class="mt-4">
        <InputLabel for="family_name" :value="$t('family_name')" />
        <TextInput
          id="family_name"
          v-model="form.family_name"
          type="text"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="family_name"
        />
        <InputError class="mt-2" :message="form.errors.family_name" />
      </div>
      <div class="mt-4">
        <InputLabel for="registration_code">
          {{ $t("registration_code") }}
          <a-tooltip placement="topRight">
            <template #title>
              <span>Acquired from your organization admin.</span>
            </template>
            <QuestionCircleOutlined />
          </a-tooltip>
        </InputLabel>

        <TextInput
          id="registration_code"
          v-model="form.registration_code"
          type="text"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="registration_code"
        />
        <InputError class="mt-2" :message="form.errors.registration_code" />
      </div>
      <div class="mt-4">
        <InputLabel for="email" :value="$t('email')" />
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          required
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" :value="$t('password')" />
        <TextInput
          id="password"
          v-model="form.password"
          type="password"
          class="mt-1 block w-full"
          required
          autocomplete="new-password"
        />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div class="mt-4">
        <InputLabel for="password_confirmation" :value="$t('confirm_password')" />
        <TextInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="mt-1 block w-full"
          required
          autocomplete="confirm-password"
        />
        <InputError class="mt-2" :message="form.errors.organization_id" />
      </div>

      <!-- <div class="mt-4">
                <InputLabel for="organization" value="Organization" />
                <TextInput
                    id="organization"
                    v-model="form.organization_id"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="organization"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div> -->

      <div v-if="$page.props.hasTermsAndPrivacyPolicyFeature" class="mt-4">
        <InputLabel for="terms">
          <div class="flex items-center">
            <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

            <div class="ml-2">
              {{ $t("I_agree_to_the") }}
              <a
                target="_blank"
                :href="route('terms.show')"
                class="underline text-sm text-gray-600 hover:text-gray-900"
                >{{ $t("terms_of_service") }}</a
              >
              {{ $t("and") }}
              <a
                target="_blank"
                :href="route('policy.show')"
                class="underline text-sm text-gray-600 hover:text-gray-900"
                >{{ $t("privacy_policy") }}</a
              >
            </div>
          </div>
          <InputError class="mt-2" :message="form.errors.terms" />
        </InputLabel>
      </div>

      <div class="flex items-center justify-end mt-4">
        <Link
          :href="route('login')"
          class="underline text-sm text-gray-600 hover:text-gray-900"
        >
          {{$t('already_registered')}}?
        </Link>
        <PrimaryButton
          class="ml-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          {{$t('register')}}
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
