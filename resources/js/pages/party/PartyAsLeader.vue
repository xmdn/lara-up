<template>
  <div class="party-right__container">
    <div class="inbox--top">
      <div class="inbox--top-left">
        <span class="iconify inbox--left-icon" data-icon="carbon:3rd-party-connected" />
        <h2 class="inbox--heading">
          Party
        </h2>
      </div>
      <!-- <div><span class="iconify inbox--right-icon" data-icon="ic:round-filter-list" /></div> -->
    </div>
    <div class="inbox--container">
      <div v-if="party && party.leader && party.leader.members.length !== 0" class="member-item__list">
        <MemberItem v-for="(member, index) in party.leader.members" :key="`MemberItem-${member.id}`" :data="member" :index="index" @click="kick" />
      </div>
      <p v-else class="info__p">
        Let's invite someone to your party! :D
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { mapGetters, mapActions } from 'vuex'
import MemberItem from '~/components/Party/MemberItem'

export default {
  name: 'PartyAsLeaderPage',
  scrollToTop: false,

  components: { MemberItem },

  metaInfo () { return { title: 'Party as Leader' } },

  computed: {
    ...mapGetters({
      party: 'auth/party',
      snackbar: 'notification/snackbar'
    })
  },

  methods: {
    ...mapActions('notification', ['attachSnackbar']), // Map the action
    triggerSnackbar(message) {
      this.attachSnackbar({ message }); // Dispatch the action
    },
    async kick (index) {
      const member = this.party.leader.members[index]

      await axios.post('/api/party/kick', {
        member_id: member.member_id,
        member_name: member.member.full_name
      }).then(({ data }) => {
        console.log(data)
        this.triggerSnackbar(data.message)
        this.$store.dispatch('auth/updateUserParty', {
          leader: data.leader
        })
      })
    }
  }
}
</script>
