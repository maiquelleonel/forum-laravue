<template>
    <div>
        <div class="card horizontal"
             v-for="reply in data"
             :class="{ 'yellow lighten-5' : !!reply.highlighted }"
        >
            <div class="card-images">
                <img :src="'/'+reply.user.photo" alt="">
            </div>
            <div class="card-stacked">
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
        </div>
        <div class="card grey lighten-4" v-if="thread_closed == 0">
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
            'replied','reply','yourAnswer','send','threadId', 'highlight','threadOwner','threadClosed'
        ],
        data(){
            return {
                data: [],
                thread_id: this.threadId,
                thread_closed: this.threadClosed,
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
                    this.data = res.data;
                })
            }
        },
        mounted() {
            this.getReplies()
            console.log(this.data)
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
