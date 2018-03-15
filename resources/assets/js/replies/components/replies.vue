<template>
    <div>
        <div class="card">
            <div class="card-content">
                <span class="card-title">FOOOOOO {{ replied }}</span>
                <blockquote>
                    DEUUUUUUUUUUUU CARAAAAAI
                </blockquote>
            </div>
        </div>
        <div class="card grey lighten-4">
            <div class="card-content">
                <span class="card-title">{{ yourAnswer }}</span>
                <form action="">
                    <div class="input-field">
                        <textarea class="materialize-textarea" rows="10" :placeholder="reply"></textarea>
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
            'replied','reply','yourAnswer','send'
        ]
    }
</script>
