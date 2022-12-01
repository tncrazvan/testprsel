<script lang="ts">
  export let path: string
  export let size: string = '1.6rem'
  export let color = 'currentColor'
  export let stroke = 'none'
</script>

<svg
  xmlns="http://www.w3.org/2000/svg"
  style="width:{size};height:{size}"
  fill={color}
  viewBox="0 0 24 24"
  stroke={stroke}
  ><path
    stroke-linecap="round"
    stroke-linejoin="round"
    stroke-width="1"
    d={path}
  /></svg
>