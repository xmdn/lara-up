<template>
  <div class="forgot-password__container">
    <TopImage :type="1" />
    <h2 class="forgot-password__h2 mb-1_5">
      Reset your password!
    </h2>

    <p class="mb-3">
      One more step to reset your account password! Just fill the new password you want to on field down below.
    </p>

    <form class="settings-form--container" @submit.prevent="reset" @keydown="form.onKeydown($event)">
      <div class="login-input--container">
        <input v-model="form.password" class="login-input" placeholder="New Password" type="password" required>
        <has-error :form="form" field="password" />
      </div>

      <div class="login-input--container">
        <input v-model="form.password_confirmation" class="login-input" placeholder="Re-Type New Password" type="password" required>
        <has-error :form="form" field="password_confirmation" />
      </div>

      <v-button :loading="form.busy" class="btn btn--blue ml-auto mt-auto">
        Reset Password
      </v-button>
    </form>
  </div>
</template>

<script>
import Form from 'vform'
import { mapGetters, mapActions } from 'vuex'

export default {
  middleware: 'guest',

  metaInfo () { return { title: 'Reset Password' } },

  data: () => ({
    status: '',
    form: new Form({
      token: '',
      email: '',
      password: '',
      password_confirmation: ''
    })
  }),

  computed: {
    ...mapGetters({
      snackbar: 'notification/snackbar'
    })
  },

  created () {
    this.form.email = this.$route.query.email
    this.form.token = this.$route.params.token
  },

  methods: {
    ...mapActions('notification', ['attachSnackbar']), // Map the action
    triggerSnackbar(message) {
      this.attachSnackbar({ message }); // Dispatch the action
    },
    async reset () {
      this.form.post('/api/password/reset')
        .then(({ data }) => {
          this.triggerSnackbar(data.message)
          this.$router.push({ name: 'login' })
        })
        .catch(e => {
          this.triggerSnackbar(e.response.data.email)
          this.$router.push({ name: 'password.request' })
        })
    }
  }
}
</script>
