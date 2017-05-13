<template>
   <div class="datePicker clearfix">
       <div class="popup-close popup-close-small" @click="date.visible = false">&times;</div>
        <h4>{{ date.calendarDate() }}</h4>

        <ul class="prevNextMonth clearfix">
            <li @click="displayPreviousMonth">&larr;</li>
            <li @click="date.displayNextMonth()">&rarr;</li>
        </ul>

        <div class="weekdays">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>

        <div v-for="day in date.days" :class="day.className"
            @click="setDate(day.year, day.month, day.day)">
            {{ day.day }}
        </div>

    </div>
</template>

<script>

    export default {

        props: {
           date: { required: true }
        },

        methods: {

            setDate(day, month, year) {
                this.date.set(day, month, year);
                this.date.hide();
            },

            displayPreviousMonth() {
                this.date.displayPreviousMonth();
            }
        }
    }
</script>
