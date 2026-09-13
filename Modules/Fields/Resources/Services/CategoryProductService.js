import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  try {
    const response = await datatable.list(route('category_products.index'), lazyParams);
    return response.data;
  } catch (error) {
    console.error(error);
    return false;
  }
};

const create = async (data) => {
  await axios.post(route('category_products.store'), data);
};

const update = async (id, data) => {
  await axios.put(route('category_products.update', { id }), data);
};

const del = async (id) => {
  await axios.delete(route('category_products.destroy', { id }));
  return true;
};

export default {
  list,
  create,
  update,
  del,
};
