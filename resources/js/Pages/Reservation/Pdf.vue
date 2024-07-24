<template>
    <div>
      <iframe :src="pdfUrl" style="width: 100%; height: 800px;" frameborder="0"></iframe>
      <button @click="print">Print</button>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue'
  
  const props = defineProps({
    pdfContent: String
  })
  
  const pdfUrl = computed(() => `data:application/pdf;base64,${props.pdfContent}`)
  
  const print = () => {
    const iframe = document.createElement('iframe')
    iframe.style.display = 'none'
    iframe.src = pdfUrl.value
    document.body.appendChild(iframe)
    iframe.contentWindow.print()
  }
  </script>
  