/**
 * Collection.js
 *
 * Collection is an enhanced data model, much like an extended array. It is
 * inspired by Laravel's Collection class.
 */

class Collection {

    
    /**
     * Create a new Collection
     * @param mixed - array, Resources\Assets\Js\Lib\Collection
     */
    constructor(data) {
		if (data instanceof Collection) {
			data = data.originalData;
		}

        this.data = data;
		this.originalData = data;
        this.groupedBy = null;
    }

    /**
     * Clone an item in the collection
     * @param mixed - number or string key-value from the existing collection
     */

	clone(item) {
		let source;
		let clone = {};

		if (typeof item === 'number' || typeof item === 'string') {
			source = this.data[item];

		} else if (typeof item === 'object') {
			source = item;
		}

		for (let property in source) {
			if (typeof property === 'object') {
				clone[property] = this.clone(source[property]);

			} else {
				clone[property] = source[property];
			}
		}

		return clone;
	}

	collapse(data = null, destruct = false) {
        var store = [];

		// Explicitly collapse some data
        if (data !== null) {
            data.forEach(item => {
                if (Array.isArray(item)) {
                    return store = store.concat(this.collapse(item, true));
                }

                if (typeof item === 'object') {
                    store.push(item);
                }
            });
            return store;
        }

		this.each(item => {
			if (Array.isArray(item)) {
                return store = store.concat(this.collapse(item, true));
            }

            if (typeof item === 'object') {
                store.push(item);
            }
		});

		if (destruct) {
			this.data = store;
			this.groupedBy = null;
	        return this;
		}

        return new Collection(store);
    }

    count() {
		return this.data instanceof Object ?
			Object.keys(this.data).length :
			this.data.length;
    }

	countAll(){
		return this.originalData.length;
	}

    first() {
        return this.data[0];
    }

	has(item) {
		return this.data.indexOf(item) !== -1;
	}

    last() {
        return this.data[this.data.length - 1];
    }

    each(func) {
		if (Array.isArray(this.data)) {
			this.data.forEach((item, index, array) => {
	            func(item, index, array);
	        });

		} else {
			for (let key in this.data) {
				func(this.data[key], key);
			}
		}

        return this;
    }

	forget(index) {
		this.data.splice(index, 1);
	}

    filter(func) {
        return new Collection (
			this.data.filter((item, index, array) => {
	            return func(item, index, array);
	        })
		);
    }

	find(id) {
		return this.where('id', id);
	}

	isEmpty() {
		return this.count() === 0;
	}

	isNotEmpty() {
		return ! this.isEmpty();
	}

	isGrouped() {
		return this.groupedBy !== null;
	}

    groupBy(property) {

		if (this.groupedBy === property) {
			return;
		}

		if (this.isGrouped()) {
			this.collapse(null, true);
		}

        let data = {};

        this.each(item => {
            let index = item[property];

            if (data[index] === undefined) {
                data[index] = [];
            }

            data[index].push(item);
			data[index]._grouped = true;
        });

        this.groupedBy = property;
        this.data = data;

        return this;
    }

    map(func) {
        return new Collection (
				this.data.map((item, index, array) => {
	            return func(item, index, array);
	        })
		);
    }

	put(item, index) {
		this.data.splice(index, 0, item);
	}

	pluck(property) {
		return this.map(item => {
			return item[property];
		});
	}

    reduce(func, startValue = 0) {
        return this.data.reduce((carry, value) => {
            return func(carry, value);
        }, startValue);
    }

	reset() {
		this.data = this.originalData;
	}

	sum(property) {
		return this.reduce((carry, item) => {
			return carry + parseInt(item[property]);
		});
	}

	unique(property = null) {
		let exists = [];

		return this.filter(item => {
			let value = property === null ? item : item[property];

			if (exists.indexOf(value) !== -1) {
				return false;
			}

			exists.push(value);

			return true;
		})
	}

    where(property, comparisionOrValue, value = null) {
		if (property instanceof Object) {
			return this.whereMultiple(property);
		}

        var comparison;

        if (!value) {
            value = comparisionOrValue;
            comparison = '=';
        } else {
            comparison = comparisionOrValue;
        }

        return new Collection (
			this.filter( item => {
	            if (comparison === '=')  { return item[property] == value; }
	            if (comparison === '!=') { return item[property] != value; }
	            if (comparison === '>')  { return item[property] >  value; }
	            if (comparison === '<')  { return item[property] <  value; }
	            if (comparison === '>=') { return item[property] >= value; }
	            if (comparison === '<=') { return item[property] <= value; }
	            return item[property] === value;
	        })
		);
    }

	whereIn(property, values = []) {
		return this.filter(item => {
			return values.indexOf(item[property]) !== -1;
		});
	}

	whereMultiple(properties) {
		let collection = new Collection(this.data);

		for (let property in properties) {
			collection = collection.where(property, properties[property]);
		}

		return collection;
	}

	use() {
		return this.data;
	}
}

export default Collection;
