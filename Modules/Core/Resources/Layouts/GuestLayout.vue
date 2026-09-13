<script setup>

</script>

<template>
  <div id="container-guest" class="h-screen px-8 flex items-center justify-center bg-[#cc6844] dark:bg-gray-900">
    <div class="w-full md:max-w-lg p-6 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-md shadow-md border lg:max-w-md">
      <slot />
    </div>
  </div>
</template>
