<template>
  <div class="post">
    <div v-if="loading" class="loading">Loading...</div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <HostTable ref="stbl" :tabledata="tabledata" :header="header" :config="config">
      <div slot="edit_row">
        <pre>{{ current_edit_row }}</pre>
        <p>
          <button @click.prevent="$refs.stbl.close()">Cancel</button>
        </p>
      </div>
    </HostTable>
  </div>
</template>

<script>
import axios from 'axios'
import HostTable from '../components/HostTable'

export default {
  name: 'IndexPage',
  components: {
    HostTable
  },
  data () {
    return {
      loading: true,
      error: true,
      current_edit_row: {},
      config: {
        // show_increment: true,
        context_menu: true,
        edit_row: false,
        table_class: 'table margin-0'
      },
      header: [
        { title: 'Id' },
        { title: 'Node' },
        { title: 'Name' },
        { title: 'State' }
      ],
      tabledata: []
    }
  },
  mounted () {
    this.machines = this.fetchData()
  },
  created () {
    // fetch the data when the view is created and the data is
    // already being observed
    this.fetchData()
  },
  methods: {
    async fetchData () {
      this.error = null
      const nodeData = await axios.get('https://stats.nowlive.ro/api/')
      console.log(nodeData.data.machines)
      this.loading = false
      this.tabledata = nodeData.data.machines
      return nodeData.data.machines
    }
  }
}
</script>

<style lang="scss" scoped>
.table {
  width:100%;
}

.some-modal-content {
  min-width: 400px;
  padding: 25px;

  .buttons button {
    padding: 10px;
    margin: 10px;
  }
}
</style>
