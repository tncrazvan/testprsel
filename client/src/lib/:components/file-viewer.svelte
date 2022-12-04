<script lang="ts">
  import Icon from ':components/icon.svelte'
  import ResultsList from ':components/results-list.svelte'
  import type { ParserResult } from ':types/parser-result'
  import type { ServerResponse } from ':types/server-response'
  import { mdiUpload } from '@mdi/js'
  import axios from 'axios'
  import { navigate } from 'svelte-routing'
  const parse = () =>
    axios.get<ServerResponse<Array<ParserResult>>>('/api/repository')
  let index = 0
</script>

<div
  class="
    btn
    btn-primary
    fixed
    right-32
    top-2
    z-10
  "
  style="pointer-events: all;"
  on:mousedown={() => navigate('/')}
>
  <Icon path={mdiUpload} />
  <span>Upload a new file</span>
</div>

{#await parse()}
  <progress class="progress w-56" />
{:then r}
  {@const results = r.data.data}
  <div class="pt-4" />
  <div class="tabs tabs-boxed rounded-3xl" style="width: 50rem;">
    <span class="tab" class:tab-active={index === 0} on:mousedown={() => (index = 0)}>
      <span>Acceptable</span>
    </span>
    <span class="tab" class:tab-active={index === 1} on:mousedown={() => (index = 1)}>
      <span>Successful corrections</span>
    </span>
    <span class="tab" class:tab-active={index === 2} on:mousedown={() => (index = 2)}>
      <span>Failed corrections</span>
    </span>
  </div>

  <div class="relative overflow-hidden p-4" style="width: 50rem;">
    {#if index === 0}
      <ResultsList
        type="acceptable"
        items={r.data.data.filter(item => item.corrected === item.original)}
      />
    {:else if index === 1}
      <ResultsList
        type="successful-corrections"
        items={r.data.data.filter(
          item => item.corrected !== item.original && item.isCorrect
        )}
      />
    {:else if index === 2}
      <ResultsList
        type="failed-corrections"
        items={r.data.data.filter(
          item => item.corrected !== item.original && !item.isCorrect
        )}
      />
    {/if}
  </div>
{:catch error}
  <div class="text-center">
    <span>{error}</span>
  </div>
{/await}
