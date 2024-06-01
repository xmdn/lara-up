<template>
  <div id="content">
    <!-- <loading ref="loading" /> -->
    <div v-if="!$matchMedia.xl">
      <snackbar ref="snackbar" base-size="10rem" position="bottom" :hold-time="5000" :multiple="false" />
    </div>
    <div v-else>
      <snackbar ref="snackbar" base-size="10rem" style="font-size:1.24rem;top:10%;right:7%;" position="top-right" :hold-time="5000" :multiple="false" />
    </div>
    <transition name="page" mode="out-in">
      <component :is="layout" v-if="layout" />
    </transition>
  </div>
</template>

<script>
// import Loading from './Loading'
import { useRoute } from 'vue-router';
import { loadProgressBar } from 'axios-progress-bar'
import { watch } from 'vue'; // Import watch from Vue
import { mapGetters, mapActions } from 'vuex';
// import { inject } from 'vue';

loadProgressBar()

const requireContext = require.context('~/layouts', false, /.*\.vue$/)

const layouts = requireContext.keys()
  .map(file =>
    [file.replace(/(^.\/)|(\.vue$)/g, ''), requireContext(file)]
  )
  .reduce((components, [name, component]) => {
    components[name] = component.default || component
    return components
  }, {})

export default {
  el: '#app-content',

  computed: {
    ...mapActions({
      attachSnackbar: 'notification/attachSnackbar'
    }),
    ...mapGetters({
      isUserLoaded: 'auth/userLoaded',
      snackbarget: 'notification/snackbar',
      // snack: 'notification/snack'
    })
  },
  data: () => ({
    layout: null,
    defaultLayout: 'default'
  }),

  metaInfo () {
    const appName = 'PHive'

    return {
      title: appName,
      titleTemplate: `%s · ${appName}`
    }
  },

  mounted () {
    this.fetchUserData(); // Call the async function
    this.initStore();
    this.setLayout();
  },
  

  methods: {
    async initStore() {
      // Assuming the store might be asynchronously loaded
      while (!this.$store) {
        await new Promise(resolve => setTimeout(resolve, 100)); // Wait 100ms and check again
      }
      if (this.$store) {
        this.$store.dispatch('notification/attachSnackbar', {
          snackbar: this.$refs.snackbar
        });
      }
    },

    setLayout(layout) {
      if (!layout || !layouts[layout]) {
        layout = this.defaultLayout
      }

      this.layout = layouts[layout]
    },
    async fetchUserData() {
      try {
        await this.$store.dispatch('auth/fetchUser');
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    }
  },
  watch: {
    snackbarget(newValue) {
      if (newValue) {
        this.$refs.snackbar.open(newValue);
      }
    }
  },
}
</script>
