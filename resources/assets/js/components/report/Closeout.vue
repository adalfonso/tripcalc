<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="popup report closeoutReport">
            <div class="popup-close" @click="hide">&times;</div>
            <h4 class="centered">Closeout Report</h4>
            <hr>

            <div v-for="spender in spendersByLastName" class="spenderGroup">
                <h5>
                    <strong>
                        {{ spender.last_name }}, {{ spender.first_name}}
                    </strong>
                </h5>
                <p class="description">{{ description(spender) }}</p>

                <p v-for="(credit, id) in spender.credits" class="line">
                    <span>${{ currency(credit) }} &larr;</span>
                    {{ allUsers[id] }}
                </p>

                <p v-for="(debit, id) in spender.debits" class="line">
                    <span>${{ currency(debit) }} &rarr;</span>
                    {{ allUsers[id] }}
                </p>
                <hr>
            </div>
        </div>
    </div>
</template>

<script>

export default {

    props: {
        trip_id: { default: null }
    },

    data() {
        return {
            spenders: [],
            allUsers: {}
        };
    },

	created() {
		axios.get(`/trip/${ this.trip_id }/report/closeout`)
        .then(response => {
           this.spenders = response.data.spenders;
           this.allUsers = response.data.allUsers;
        });
	},

    computed: {
        spendersByLastName (){
            return this.spenders.sort(function(a, b) {
                let person1 = a.last_name + ' ' + a.first_name;
                let person2 = b.last_name + ' ' + b.first_name;

                if(person1 < person2) {
                    return -1;
                }

                if(person1 > person2) {
                    return 1;
                }

                return 0;
            });
        }
    },

    methods: {
        hide() {
            this.$emit('hide');
        },

        currency(amount) {
            return Number(amount).toFixed(2);
        },

        description (spender) {
            if (this.length(spender.credits) + this.length(spender.debits) === 0 ) {
                return 'Perfectly square.';
            }

            return this.length(spender.credits) > 0
            ? 'Paid by ' + this.length(spender.credits)
            : 'Pays ' + this.length(spender.debits);
        },

        length(data) {
            return Object.keys(data).length;
        }
    }
}
</script>
