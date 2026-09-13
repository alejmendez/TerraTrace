<script setup>

</script>

<template>
  <div id="container-guest" class="min-h-screen px-5 flex items-center justify-center bg-[#f2f8f1] dark:bg-[#111d16]">
    <div class="w-full lg:max-w-md">
      <div class="mb-7 flex items-center justify-center gap-3 text-[#173e2e] dark:text-white">
        <span class="grid h-10 w-10 place-items-center rounded-[50%_50%_50%_12%] bg-[#26713d] text-white rotate-[-16deg]">
          <span class="material-symbols-rounded rotate-[16deg]">spa</span>
        </span>
        <div>
          <p class="m-0 text-lg font-extrabold tracking-[-0.04em]">TerraTrace</p>
          <p class="m-0 text-[11px] font-semibold text-[#65776e] dark:text-[#b8c8bd]">Gestión de cosecha</p>
        </div>
      </div>
      <div class="rounded-xl border border-[#dfebdf] bg-white p-7 shadow-[0_14px_45px_rgba(27,72,43,0.12)] dark:border-[#385443] dark:bg-[#1b2d22]">
        <slot />
      </div>
    </div>
  </div>
</template>
