<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Link, useForm } from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
  user: Object,
});

const form = useForm({
  _method: "PUT",
  name: props.user.name,
  email: props.user.email,
  photo: props.user.photo,
  language: props.user.language,
  locale: props.user.locale,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
  if (photoInput.value) {
    form.photo = photoInput.value.files[0];
  }

  form.post(route("user-profile-information.update"), {
    errorBag: "updateProfileInformation",
    preserveScroll: true,
    onSuccess: () => clearPhotoFileInput(),
  });
};

const sendEmailVerification = () => {
  verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
  photoInput.value.click();
};

const updatePhotoPreview = () => {
  const photo = photoInput.value.files[0];

  if (!photo) return;

  const reader = new FileReader();

  reader.onload = (e) => {
    photoPreview.value = e.target.result;
  };

  reader.readAsDataURL(photo);
};

const deletePhoto = () => {
  router.delete(route("current-user-photo.destroy"), {
    preserveScroll: true,
    onSuccess: () => {
      photoPreview.value = null;
      clearPhotoFileInput();
    },
  });
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};
</script>

<template>
  <FormSection @submitted="updateProfileInformation">
    <template #title>
      {{ $t("account_information") }}
    </template>

    <template #description>
      {{ $t("account_information_message") }}
    </template>

    <template #form>
      <!-- Profile Photo -->
      <div
        v-if="$page.props.managesProfilePhotos"
        class="col-span-6 sm:col-span-4"
      >
        <!-- Profile Photo File Input -->
        <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" />

        <InputLabel for="photo" :value="$t('photo')" />

        <!-- Current Profile Photo -->
        <div v-show="!photoPreview" class="mt-2">
          <img
            :src="user.profile_photo_url"
            :alt="user.name"
            class="rounded-full h-20 w-20 object-cover"
          />
        </div>

        <!-- New Profile Photo Preview -->
        <div v-show="photoPreview" class="mt-2">
          <span
            class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
            :style="'background-image: url(\'' + photoPreview + '\');'"
          />
        </div>

        <SecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewPhoto">
          {{ $t("select_a_new_photo") }}
        </SecondaryButton>

        <SecondaryButton
          v-if="user.profile_photo_path"
          type="button"
          class="mt-2"
          @click.prevent="deletePhoto"
        >
          {{ $t("remove_photo") }}
        </SecondaryButton>

        <InputError :message="form.errors.photo" class="mt-2" />
      </div>

      <!-- Name -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="name" :value="$t('name')" />
        <TextInput
          id="name"
          v-model="form.name"
          type="text"
          class="mt-1 block w-full"
          autocomplete="name"
        />
        <InputError :message="form.errors.name" class="mt-2" />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="email" :value="$t('email')" />
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
        />
        <InputError :message="form.errors.email" class="mt-2" />

        <div
          v-if="
            $page.props.hasEmailVerification && user.email_verified_at === null
          "
        >
          <p class="text-sm mt-2">
            {{ $t("email_unverified") }}

            <Link
              :href="route('verification.send')"
              method="post"
              as="button"
              class="underline text-gray-600 hover:text-gray-900"
              @click.prevent="sendEmailVerification"
            >
              {{ $t("click_verification_email") }}
            </Link>
          </p>

          <div
            v-show="verificationLinkSent"
            class="mt-2 font-medium text-sm text-green-600"
          >
            {{ $t("sent_verification_link") }}
          </div>
        </div>
      </div>

      <!-- Locle -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="language" :value="$t('prefer_language')" />
        <select
          v-model="form.language"
          class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full"
        >
          <option value="zh-TW" class="h-2">{{ $t("chinese") }}</option>
          <option value="en">{{ $t("english") }}</option>
        </select>
        <InputError :message="form.errors.language" class="mt-2" />
      </div>
    </template>

    <template #actions>
      <ActionMessage :on="form.recentlySuccessful" class="mr-3"> Saved. </ActionMessage>

      <PrimaryButton
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
      >
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>
