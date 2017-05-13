import Queue from '../lib/Queue.js';

class FriendQueue extends Queue {

    add(data) {
        let type = (data.item === null) ? 'email' : 'id';

        if (type === 'email' && !this.isValidEmail(data.input)) {
            return {
                success: false, response: 'You have entered an invalid email.'
            };
        }

        let newItem = {
            type: type,
            data: (type === 'id') ? data.item.id : data.input,
            display: (type === 'id') ?
                data.item.first_name + ' ' + data.item.last_name :
                data.input
        };

        return super.add(newItem);
    }

    contains(item) {
        let index = this.items.findIndex( function(element) {
            return element.type === item.type &&
                element.data === item.data;
        });

        return index !== -1;
    }

    isValidEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    userIds() {
        return this.items.filter( user => {
            return user.type === 'id';
        }).map( user => {
            return user.data;
        });
    }

}

export default FriendQueue;
