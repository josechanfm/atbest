<template>
    <div class="w-screen min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
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
