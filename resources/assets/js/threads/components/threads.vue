<template>
    <div class="card">
        <div class="card-content">
            <span class="card-title">{{ title }}</span>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ thread }}</th>
                        <th>{{ replies }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="thread in threads_res.data" :class="{ 'yellow lighten-5' : !!thread.pinned }">
                        <td>{{ thread.id }}</td>
                        <td>
                            <a :href="'/threads/' + thread.id">
                                <i class="material-icons"
                                   style="color:red"
                                   v-if="!!thread.pinned"
                                >bookmark</i>
                                {{ thread.title }}
                            </a>
                        </td>
                        <td>{{ thread.total_replies }}</td>
                        <td v-if="!!can_pin">
                            <a :href="'/threads/'+ thread.id +'/pinner'"
                                class="waves-effect btn"
                            >
                                <span v-if="!!thread.pinned">{{ unpin }}</span>
                                <span v-else>{{ pin }}</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-content">
            <span class="card-title">{{ newThread }}</span>
            <form @submit.prevent="save">
                <div class="input-field">
                    <input type="text" :placeholder="threadTitle" v-model="new_thread.title"/>
                </div>
                <div class="input-field">
                    <textarea class="materialize-textarea" :placeholder="threadBody"
                              v-model="new_thread.body"></textarea>
                </div>
                <button type="submit" class="btn red accent-2">{{ send }}</button>
            </form>

        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'title','thread','replies','newThread','threadTitle','threadBody','send','canPin', 'pin', 'unpin'
        ],
        data() {
            return {
                threads_res: [],
                can_pin: this.canPin || false,
                new_thread: {
                    title:'',
                    body:''
                }
            }
        },
        methods: {
            save(){
                window.axios.post('/threads', this.new_thread ).then((res) => {
                    this.getThreads()
                })
            },
            getThreads() {
                window.axios.get('/threads').then((res) => {
                    this.threads_res = res.data
                })
            }
        },
        mounted() {
            this.getThreads()
            Echo.channel('new.thread')
                .listen('NewThread', (e) => {
                    console.log(e)
                    if( e.thread ) {
                        this.threads_res.data.unshift(e.thread)
                    }
                })
        }
    }
</script>
