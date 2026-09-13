import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  try {
    const response = await datatable.list(route('users.index'), lazyParams);
    return response.data;
  } catch (error) {
    console.error(error);
    return false;
  }
};

const del = async (id) => {
  try {
    await axios.delete(route('users.destroy', { id }));
    return true;
  } catch (error) {
    console.error(error);
    return false;
  }
};

export default {
  list,
  del,
};
