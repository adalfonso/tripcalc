import Collection from './Collection.js';

class Split {

    constructor(travelers = {}, user = null, type = '') {
        this.options = { even: {}, personal: {}, custom: {} };
        this.travelers = new Collection(travelers);
        this.user = user;
        this.type = type;
    }

    even() {
        this.reset('even');
    }

    personal() {
        this.reset('personal');

        this.getUser().is_spender = true;
        this.getUser().split_ratio = 1;
    }

    custom() {
        this.reset('custom');
    }

    is(type) {
        return this.type === type;
    }

    interpret() {
        let isSpender = this.getUser().is_spender;

        let spenders  = this.travelers.where('is_spender', true);

        if (spenders.isEmpty()) {
            return this.type = 'even';

        } else if (spenders.count() === 1 && isSpender) {
            return this.type = 'personal';
        }

        return this.type = 'custom';
    }

    toggle(index) {
        this.travelers.data[index].is_spender = !this.travelers.data[index].is_spender;
    }

    getUser() {
        return this.travelers.find(this.user);
    }

    reset(type) {
        this.type = type;

        this.travelers.each(user => {
            user.is_spender = false;
            user.split_ratio = null;
        });
    }
}

export default Split;
