import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  try {
    const response = await datatable.list(route('owners.index'), lazyParams);
    return response.data;
  } catch (error) {
    console.error(error);
    return false;
  }
};

const create = async (data) => {
  await axios.post(route('owners.store'), data);
};

const update = async (id, data) => {
  await axios.put(route('owners.update', { id }), data);
};

const del = async (id) => {
  await axios.delete(route('owners.destroy', { id }));
  return true;
};

export default {
  list,
  create,
  update,
  del,
};
