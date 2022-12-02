<script lang="ts">
  import Icon from ':components/icon.svelte'
  import type { ParserResult } from ':types/parser-result'
  import type { ServerResponse } from ':types/server-response'
  import { mdiUpload } from '@mdi/js'
  import axios from 'axios'
  import { navigate } from 'svelte-routing'
  const parse = () =>
    axios.get<ServerResponse<Array<ParserResult>>>('/api/repository/parse')
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
  on:mousedown={() => navigate('/')}
>
  <Icon path={mdiUpload} />
  <span>Upload a new file</span>
</div>

{#await parse()}
  <progress class="progress w-56" />
{:then r}
  {@const results = r.data.data}
  <div class="fixed left-0 right-0 top-0 bottom-0 grid grid-cols-2">
  <div class="overflow-x-auto">
    <div class="text-start p-4">
      <span class="text-3xl text-success">Successful corrections</span>
    </div>
    <table class="table table-zebra table-compact h-full w-full">
      <thead>
        <tr>
          <td>ID</td>
          <td>Original</td>
          <td>Corrected</td>
          <td>Removed</td>
        </tr>
      </thead>
      <tbody>
        {#each results.filter(r => r.isCorrect) as result}
          <tr>
            <td>{result.id}</td>
            <td>{result.original}</td>
            <td>{result.corrected}</td>
            <td>
              {#each result.removed as removed}
                <span class="text-green-600">{result.removed}</span>
                <div class="pt-2" />
              {/each}
            </td>
          </tr>
        {/each}
      </tbody>
      <thead>
        <tr>
          <th>ID</th>
          <th>Original</th>
          <th>Corrected</th>
          <th>Removed</th>
        </tr>
      </thead>
    </table>
  </div>
  <div class="overflow-x-auto">
    <div class="text-start p-4">
      <span class="text-3xl text-error">Failed corrections</span>
    </div>
    <table class="table table-zebra table-compact h-full w-full">
      <thead>
        <tr>
          <td>ID</td>
          <td>Original</td>
          <td>Corrected</td>
          <td>Removed</td>
        </tr>
      </thead>
      <tbody>
        {#each results.filter(r => !r.isCorrect) as result}
          <tr>
            <td>{result.id}</td>
            <td>{result.original}</td>
            <td>{result.corrected}</td>
            <td>
              {#each result.removed as removed}
                <span class="text-red-800">{result.removed}</span>
                <div class="pt-2" />
              {/each}
            </td>
          </tr>
        {/each}
      </tbody>
      <thead>
        <tr>
          <th>ID</th>
          <th>Original</th>
          <th>Corrected</th>
          <th>Removed</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
{:catch error}
  <div class="text-center">
    <span>{error}</span>
  </div>
{/await}
