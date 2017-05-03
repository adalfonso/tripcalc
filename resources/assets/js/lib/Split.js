class Split {

    constructor(travelers = {}, user = null, type = '') {
        this.options = { even: {}, personal: {}, custom: {} };
        this.travelers = travelers;
        this.user = user;
        this.type = type;
    }

    even() {
        this.reset('even');
    }

    personal() {
        this.reset('personal');
        this.travelers[this.user].is_spender = true;
        this.travelers[this.user].split_ratio = 1;
    }

    custom() {
        this.reset('custom');
    }

    is(type) {
        return this.type === type;
    }

    interpret() {
        let isSpender = this.travelers[this.user].is_spender;
        let spenders  = [];

        for (let id in this.travelers) {
            if (this.travelers[id].is_spender) {
                spenders.push(id);
            }
        }

        if (spenders.length === 0) {
            return this.type = 'even';

        } else if (spenders.length === 1 && isSpender) {
            return this.type = 'personal';
        }

        return this.type = 'custom';
    }

    reset(type) {
        this.type = type;

        for (let traveler in this.travelers) {
            this.travelers[traveler].is_spender = false;
            this.travelers[traveler].split_ratio = null;
        }
    }
}

export default Split;
