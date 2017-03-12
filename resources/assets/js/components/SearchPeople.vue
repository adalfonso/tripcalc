<template>
    <div id="searchFriendsWrapper">
        <div id="searchFriends">

            <img src="/img/icon/search.png">

            <input type="text" placeholder="Search for Friends"
                v-model="input" @keyup="search">

            <div id="searchResults" v-if="results.length > 0">
                <a v-for="user in results" :href="'/profile/' + user.username">
                    {{ user.first_name }} {{ user.last_name }}
                </a>
            </div>

        </div>
    </div>
</template>

<script>
    export default {

        data() {
            return {
               input: null,
               results: [],
               timeout: setTimeout(0)
            };
        },

        computed: {
            size() {
                let size = 0, key;
                for (key in this.requests) {
                    if (this.requests.hasOwnProperty(key)) size++;
                }
                return size;
            }
        },

        methods: {
            search() {
                clearTimeout(this.timeout);

                this.timeout = setTimeout(function(){
                    axios.post('/users/search', {'input': this.input})
                    .then(response => {
                        this.results = response.data;
                    })
                    .catch(err => { this.results = [] });
                }.bind(this), 200);
            }
        }
    }
</script>