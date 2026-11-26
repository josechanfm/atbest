<template>
    <footer class="bg-slate-800 text-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <img src="/storage/images/site_logo.png" class="h-12 w-auto" alt="智滙精英 Logo" />
                    <div>
                        <p class="text-lg font-bold text-white">智滙精英</p>
                        <p class="text-xs text-slate-400 tracking-widest">Atbest · NPO 精英會員平台</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <nav class="flex flex-wrap gap-x-8 gap-y-2 text-sm">
                    <inertia-link :href="route('home')" class="hover:text-white transition-colors">主頁</inertia-link>
                    <inertia-link :href="route('system.intro')" class="hover:text-white transition-colors">系統介紹</inertia-link>
                    <inertia-link :href="route('privacy.policy')" class="hover:text-white transition-colors">私隱政策及服務條款</inertia-link>
                </nav>
            </div>

            <div class="border-t border-slate-700 mt-8 pt-6 text-center text-xs text-slate-400">
                <p>© {{ new Date().getFullYear() }} 智滙精英（Atbest）· NPO 精英會員平台 · 版權所有</p>
                <p class="mt-1">Developed &amp; Maintained by <a href="http://hubis.ltd/" target="_blank" rel="noopener" class="text-slate-300 hover:text-white transition-colors">hubis.ltd</a></p>
            </div>
        </div>
    </footer>
</template>
