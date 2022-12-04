<style lang="scss">
  .grid {
    & > span {
      word-break: break-word;
    }
  }
</style>

<script lang="ts">
  import { storable } from ':scripts/storable'
  import type { ParserResult } from ':types/parser-result'

  export let items: Array<ParserResult>
  export let type: 'acceptable' | 'successful-corrections' | 'failed-corrections'

  let color = ''
  $: switch (type) {
    case 'successful-corrections':
      color = 'text-green-600'
      break
    case 'failed-corrections':
      color = 'text-red-600'
      break
  }
  const filter0 = storable<string>(`${type}-filter0`, '')
  const filter1 = storable<string>(`${type}-filter1`, '')
  const filter2 = storable<string>(`${type}-filter2`, '')
  const filter3 = storable<string>(`${type}-filter3`, '')

  const filtered = (filter0: string, filter1: string, filter2: string, filter3: string) =>
    items
      .filter(
        item => filter0 === '' || item.id.toLowerCase().includes(filter0.toLowerCase())
      )
      .filter(
        item =>
          filter1 === '' || item.original.toLowerCase().includes(filter1.toLowerCase())
      )
      .filter(
        item =>
          filter2 === '' || item.corrected.toLowerCase().includes(filter2.toLowerCase())
      )
      .filter(item => {
        const tmp = item.removed
          .reduce((prev, current) => `${prev} ${current}`, '')
          .toLowerCase()
        return filter3 === '' || tmp.includes(filter3.toLowerCase())
      })
</script>

<div class="grid grid-cols-4 w-full p-2 bg-slate-900 rounded-t-xl">
  <span class="text-sm font-bold">ID</span>
  <span class="text-sm font-bold">Original</span>
  <span class="text-sm font-bold">Corrected</span>
  <span class="text-sm font-bold">Removed</span>

  <span class="text-sm mb-4 mr-2">
    <input
      type="text"
      placeholder="search"
      class="input input-bordered input-primary w-full"
      bind:value={$filter0}
    />
  </span>
  <span class="text-sm mb-4 mr-2">
    <input
      type="text"
      placeholder="search"
      class="input input-bordered input-primary w-full"
      bind:value={$filter1}
    />
  </span>
  <span class="text-sm mb-4 mr-2">
    <input
      type="text"
      placeholder="search"
      class="input input-bordered input-primary w-full"
      bind:value={$filter2}
    />
  </span>
  <span class="text-sm mb-4 mr-2">
    <input
      type="text"
      placeholder="search"
      class="input input-bordered input-primary w-full"
      bind:value={$filter3}
    />
  </span>
</div>
<div
  class="grid grid-cols-4 w-full p-2 overflow-y-scroll bg-slate-800 rounded-b-xl"
  style="
      max-height: 30rem;
    "
>
  {#each filtered($filter0, $filter1, $filter2, $filter3) as item, i}
    {@const odd = i % 2 !== 0}
    <span class="text-sm" class:bg-slate-900={!odd}>
      <span>{item.id}</span>
    </span>
    <span class="text-sm" class:bg-slate-900={!odd}>
      <span>{item.original}</span>
    </span>
    <span class="text-sm" class:bg-slate-900={!odd}>
      <span>{item.corrected}</span>
    </span>
    <span class="text-sml" class:bg-slate-900={!odd}>
      {#each item.removed as removed}
        <span class={color}>{item.removed}</span>
        <div class="pt-2" />
      {/each}
    </span>
  {/each}
</div>
