<template>
    <div class="w-screen min-h-screen login-background flex flex-col sm:justify-center items-center pt-6 sm:pt-0 ">
        <div>
            <slot name="logo" />
        </div>
        <div class="w-full sm:max-w-md">
            <div class="m-5 p-5 bg-white shadow-md overflow-hidden rounded-lg">
                <slot />
            </div>
        </div>
    </div>
</template>

<style>

.login-background {
    background: rgb(210, 220, 230);
    background: linear-gradient(135deg, rgba(160, 175, 200, 0.3) 0%, rgba(190, 205, 220, 0.583) 25%, rgba(200, 215, 225, 0.538) 60%, rgba(210, 220, 230, 0.8) 100%);
}

</style>