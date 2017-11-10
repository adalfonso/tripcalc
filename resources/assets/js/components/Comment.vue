<template>
    <div class="comment clearfix">
        <p>
            <a :href="'/profile/' +  data.user.username" class="smoke">
                {{ data.user.first_name }}
                {{ data.user.last_name }}
            </a>
            {{ data.content }}
        </p>
        <p class="date">
            {{ data.dateForHumans }}
        </p>
    </div>
</template>

<script>
    export default {
        props: ['data']
    }
</script>
