import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  try {
    const response = await datatable.list(route('plant_types.index'), lazyParams);
    return response.data;
  } catch (error) {
    console.error(error);
    return false;
  }
};

const create = async (data) => await axios.post(route('plant_types.store'), data);

const update = async (id, data) => await axios.put(route('plant_types.update', { id }), data);

const del = async (id) => {
  await axios.delete(route('plant_types.destroy', { id }));
  return true;
};

export default {
  list,
  create,
  update,
  del,
};
