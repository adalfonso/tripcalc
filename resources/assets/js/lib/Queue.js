class Queue {

    constructor() {
        this.input = null;
        this.items = [];
    }

    add(data = this.input) {
        if (!data) {
            return false;
        }

        if (!this.contains(data)) {
            this.items.push(data);
        }

        this.input = null;
        return true;
    }

    remove(item) {
        let index = this.items.indexOf(item);

        this.items.splice(index, 1);
    }

    contains(item) {
        return this.items.indexOf(item) != -1;
    }
}

export default Queue;
