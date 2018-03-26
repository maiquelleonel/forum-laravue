<template>
    <div>
        <div class="card" v-for="reply in replies" :class="{ 'yellow lighten-5' : !!reply.highlighted }">
            <div class="card-content">
                <div class="card-title">
                    <strong>
                        {{ reply.user.name }}
                    </strong>
                    <small>{{ replied }}</small>
                </div>
                <blockquote>
                    {{ reply.body }}
                </blockquote>
                <div class="card-action" v-if="(!!thread_owner && !reply.highlighted)">
                    <a :href="'/replies/' + reply.id + '/highlighter'" class="btn btn-link">{{ highlight }}</a>
                </div>
            </div>
        </div>
        <div class="card grey lighten-4">
            <div class="card-content">
                <span class="card-title">{{ yourAnswer }}</span>
                <form @submit.prevent="save">
                    <div class="input-field">
                        <textarea
                            class="materialize-textarea"
                            rows="10"
                            :placeholder="reply"
                            v-model="new_reply.body"></textarea>
                        <button type="submit" class="btn red accent-2">{{ send }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props:[
            'replied','reply','yourAnswer','send','threadId', 'highlight','threadOwner'
        ],
        data(){
            return {
                replies: [],
                thread_id: this.threadId,
                thread_owner: this.threadOwner || false,
                new_reply: {
                    body: '',
                    thread_id: this.threadId
                }
            }
        },
        methods: {
            save(){
                window.axios.post('/threads/' + this.thread_id + '/replies', this.new_reply ).then((res) => {
                    this.getReplies()
                })
            },
            getReplies() {
                window.axios.get('/threads/' + this.thread_id + '/replies').then((res) => {
                    this.replies = res.data;
                })
            }
        },
        mounted() {
            this.getReplies()
            Echo.channel('new.reply.' + this.thread_id)
                .listen('NewReply', (e) => {
                    console.log(e)
                    if( e.reply ){
                        this.getReplies()
                    }
                })
        }
    }
</script>
