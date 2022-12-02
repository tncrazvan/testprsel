<script lang="ts">
  import FilePicker from ':components/file-picker.svelte'
  import FileViewer from ':components/file-viewer.svelte'
  import Tester from ':components/tester.svelte'
  import { Route, Router } from 'svelte-routing'
</script>

<div class="grid justify-center content-center h-full w-full">
  <Router>
    <Route path="/file-viewer" component={FileViewer} />
    <Route path="*" component={FilePicker} />
  </Router>

  <Tester />
</div>
