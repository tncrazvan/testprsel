<script lang="ts">
  import Icon from ':components/icon.svelte'
  import { fileToBase64 } from ':scripts/file-to-base64'
  import { mdiFile, mdiListBox, mdiUpload } from '@mdi/js'
  import axios from 'axios'
  import { navigate } from 'svelte-routing'
    import { append } from 'svelte/internal'
  let fileInput: HTMLInputElement
  let file: null | File = null
  let base64: string = ''
  let error: null | Error = null

  const fileChange = async (e: Event) => {
    /** @ts-ignore */
    if ((e?.target?.files?.length ?? 0) > 0) {
      /** @ts-ignore */
      file = e.target.files[0]
    } else {
      file = null
    }

    if (file) {
      base64 = await fileToBase64(file)
    } else {
      base64 = ''
    }
  }
</script>

<div
  class="
    btn
    btn-primary
    fixed
    right-32
    top-6
    z-10
  "
  style="pointer-events: all;"
  on:mousedown={() => navigate('/file-viewer')}
>
  <Icon path={mdiListBox} />
  <span>Show results</span>
</div>

<input bind:this={fileInput} type="file" class="hidden" on:change={fileChange} />

{#if error}
  <div class="text-center">
    <span>{error.message}</span>
  </div>
{/if}

<div class="pt-2" />

<div class="text-center">
  <span>{file?.name ?? 'No file selected'}</span>
</div>

<div class="pt-2" />

<div class="btn btn-primary" on:mousedown={() => fileInput.click()}>
  <Icon path={mdiFile} />
  <span>Pick a file</span>
</div>
<div class="pt-2" />
<div
  class="btn btn-primary"
  on:mousedown={async () => {
    try {
      if(!file) return

      const form = new FormData()

      form.append("file", file)

      const response = await axios.post('/api/repository', form, {
        headers: {
          'content-type': 'multipart/form-data',
        },
      })
      navigate('/file-viewer')
    } catch (e) {
      console.error(e)
      /** @ts-ignore */
      error = e
    }
  }}
>
  <Icon path={mdiUpload} />
  <span>Upload</span>
</div>
