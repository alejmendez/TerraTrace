<script setup>
import { ref, watch } from 'vue';
import { useConfirm } from 'primevue/useconfirm';

import CardSection from '@Core/Components/CardSection.vue';
import Button from '@Core/Components/Form/Button.vue';

import VEditor from '@Core/Components/Form/VEditor.vue';
import { stringToFormat, relativeTimeDifference, stringToDate } from '@Core/Utils/date.js';
import { store as storeComment, update as updateComment, destroy as destroyComment } from '@Tasks/Services/TaskCommentService';
import { deleteRowTable } from '@Core/Utils/table.js';

const props = defineProps({
  form: Object,
  responsibles: Array,
});

const form = props.form;

const confirm = useConfirm();

const loading = ref(false);
const isEditing = ref(false);

const commentField = ref(null);
const comments = ref(form.comments.filter((a) => a.comment != null));
const editCommentId = ref(null);
const editComment = ref(null);
const commentFieldError = ref(null);

const resetEditComment = () => {
  editCommentId.value = null;
  editComment.value = null;
  isEditing.value = false;
};

const storeCommentHandler = async () => {
  loading.value = true;
  commentFieldError.value = null;
  try {
    const newComment = await storeComment({
      task_id: form.id,
      comment: commentField.value,
    });
    if (newComment) {
      comments.value.push(newComment);
      commentField.value = null;
      commentFieldError.value = null;
    }
  } catch (error) {
    commentFieldError.value = error;
  } finally {
    loading.value = false;
  }
};

const editCommentHandler = (id) => {
  const commentOnCurrentEdition = comments.value.find((a) => a.id === id);
  editCommentId.value = id;
  editComment.value = commentOnCurrentEdition.comment + '';
  isEditing.value = true;
};

const updateCommentHandler = async () => {
  loading.value = true;
  commentFieldError.value = null;
  try {
    const updatedComment = await updateComment(editCommentId.value, { comment: editComment.value });
    if (updatedComment) {
      comments.value.find((a) => a.id === editCommentId.value).comment = updatedComment.comment;
      resetEditComment();
    }
  } catch (error) {
    commentFieldError.value = error;
  } finally {
    loading.value = false;
  }
};

const deleteComment = (id) => {
  deleteRowTable(
    trans,
    confirm,
    async () => {
      loading.value = true;
      const deletedComment = await destroyComment(id);
      if (deletedComment) {
        comments.value = comments.value.filter((a) => a.id !== id);
      }
      loading.value = false;
    },
    trans('task.form.comments.entity'),
  );
};

if (form.id === null) {
  watch(commentField, (newComment) => {
    form.comment = newComment;
  });
}
</script>

<template>
  <CardSection :header-text="__('task.sections.comments')" wrapperClass="" v-if="form.id === null">
    <VEditor
      id="new_comment_create_task"
      v-model="commentField"
      :options="responsibles"
      editorStyle="height: 220px"
    />
  </CardSection>

  <CardSection
    :header-text="__('task.sections.comments')"
    wrapperClass=""
    v-if="form.id !== null"
  >
    <div class="flex flex-col gap-2">
      <div v-for="comment in comments" :key="comment.id" class="my-2 ms-2">
        <div class="flex items-center gap-2">
          <img :src="comment.user_avatar" class="w-8 h-8 rounded-full" />
          <div class="flex flex-col">
            <span class="text-sm font-medium">{{ comment.user_name }}</span>
            <span
              class="text-xs text-gray-500"
              :title="stringToFormat(comment.created_at, 'dd/MM/yyyy HH:mm')"
              v-html="relativeTimeDifference(new Date(), stringToDate(comment.created_at))"
            ></span>
          </div>
        </div>
        <div v-if="isEditing && editCommentId === comment.id" class="my-2">
          <VEditor
            :id="`edit_comment_${comment.id}`"
            v-model="editComment"
            :options="responsibles"
            editorStyle="height: 220px"
          />
          <div v-if="commentFieldError !== null" class="text-xs text-red-500">{{ commentFieldError }}</div>
          <div class="flex w-full gap-2 pe-5 mt-2 justify-end">
            <Button @click="updateCommentHandler" :label="__('task.form.comments.save')" :loading="loading" />
            <Button @click="resetEditComment" severity="secondary" :label="__('task.form.comments.cancel')" :loading="loading" />
          </div>
        </div>
        <div v-else>
          <div class="text-sm font-medium my-2" v-html="comment.comment"></div>
          <div class="flex w-full gap-2 pe-5" v-if="comment.user_id === $page.props.auth.user.id">
            <div @click="editCommentHandler(comment.id)" class="text-xs text-gray-500 hover:text-gray-300 cursor-default">{{ __('task.form.comments.edit') }}</div>
            <div @click="deleteComment(comment.id)" class="text-xs text-gray-500 hover:text-gray-300 cursor-default">{{ __('task.form.comments.delete') }}</div>
          </div>
        </div>
      </div>
    </div>

    <div v-show="!isEditing">
      <div class="text-xl font-medium mt-2 ms-2">{{ __('task.form.comments.new_comment') }}</div>
      <div class="mt-2">
        <VEditor
          id="new_comment"
          v-model="commentField"
          :options="responsibles"
          editorStyle="height: 220px"
        />
        <div v-if="commentFieldError !== null" class="text-xs text-red-500">{{ commentFieldError }}</div>
      </div>
      <div class="flex w-full gap-2 pe-5 py-2 justify-end">
        <Button @click="storeCommentHandler" :label="__('task.form.comments.save')" :loading="loading" />
        <Button @click="commentField = null" severity="secondary" :label="__('task.form.comments.cancel')" :loading="loading" />
      </div>
    </div>
  </CardSection>
  <div class="h-[220px]"></div>
</template>
