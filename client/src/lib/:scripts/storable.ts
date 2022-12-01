/* eslint-disable @typescript-eslint/no-unsafe-assignment */
import { writable } from 'svelte/store'
import type { Writable } from 'svelte/store'

export function storable<T>(
  storeName: string,
  store: T,
  options: {
    serialize: (x: T) => string
    unserialize: (x: string) => T
  } = {
    serialize: (x: T) => JSON.stringify(x),
    unserialize: (x: string) => JSON.parse(x) as T,
  }
): Writable<T> {
  if (localStorage[storeName]) {
    try {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
      store = options.unserialize(localStorage[storeName])
    } catch (e) {
      console.warn(e)
    }
  }

  const result = writable(store)
  result.subscribe($result => {
    localStorage.setItem(storeName, options.serialize($result))
  })
  return result
}
