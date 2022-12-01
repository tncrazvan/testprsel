<script lang="ts">
  import Icon from ':components/icon.svelte'
  import { storable } from ':scripts/storable'
  import { menu } from ':stores/menu'
  import { message } from ':stores/message'
  import { number } from ':stores/number'
  import type { ParserResult } from ':types/parser-result'
  import type { ServerResponse } from ':types/server-response'
  import { mdiCheck, mdiClose, mdiTestTube } from '@mdi/js'
  import axios from 'axios'
  import type { Writable } from 'svelte/store'
  import { fade, fly } from 'svelte/transition'

  let result: Writable<null | ParserResult> = storable('result', null)
  const test = async () => {
    $message = ''
    try {
      const response = await axios.post<ServerResponse<ParserResult>>(
        '/api/repository/test',
        { number: $number }
      )
      $result = response.data.data
    } catch (e) {
      /** @ts-ignore */
      if ((e.response?.status ?? 500) === 406) {
        /** @ts-ignore */
        $result = e.response.data.data as ParserResult
        /** @ts-ignore */
        $message = e.response.data.message
      } else {
        $result = null
      }
    }
  }
</script>

{#if $menu}
  <div
    class="fixed left-0 right-0 top-0 bottom-0 bg-black opacity-50"
    transition:fade
    on:mousedown={() => ($menu = false)}
  />
  <div
    class="fixed top-24 right-8 h-56"
    style="width: 40rem;"
    transition:fly={{ y: -100 }}
  >
    <div class="card w-full bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title">Testing form</h2>
        <p>Input a number to submit to the server.</p>

        <!-- input -->
        <div class="form-control">
          <label class="input-group">
            <span>Value</span>
            <input
              type="text"
              placeholder="1234"
              class="input input-bordered w-full"
              bind:value={$number}
            />
          </label>
        </div>
        <!-- input -->

        <!-- result -->
        {#if $result}
          <div class="overflow-x-auto">
            <table class="table table-compact w-full">
              <thead>
                <tr>
                  <th>Correctness</th>
                  <th>Original</th>
                  <th>Corrected</th>
                  <th>Removed</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th>
                    {#if $result.isCorrect}
                      <Icon path={mdiCheck} color="green" />
                    {:else}
                      <Icon path={mdiClose} color="red" />
                    {/if}
                  </th>
                  <th>{$result.original}</th>
                  <th>{$result.corrected}</th>
                  <th>
                    <span class="text-red-600">{$result.removed}</span>
                  </th>
                </tr>
              </tbody>
            </table>
          </div>
        {/if}

        {#if message}
          <p class="text-red-700">{$message}</p>
        {/if}
        <!-- result -->

        <div class="card-actions justify-end">
          <button class="btn btn-primary" on:mousedown={test}>
            <span>Submit</span>
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}

<div class="fixed right-7 top-6 pointer-events-none ">
  <div
    class="
        relative
        btn
        btn-primary
      "
    style="pointer-events: all;"
    on:mousedown={() => ($menu = !$menu)}
  >
    <Icon path={mdiTestTube} />
    <span>Test</span>
  </div>
</div>
