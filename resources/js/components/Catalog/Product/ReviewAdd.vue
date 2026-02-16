<script setup>

import Rating from "primevue/rating";
import {Form, FormField} from "@primevue/forms";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Textarea from "primevue/textarea";
import Message from "primevue/message";
import {computed, defineProps, ref} from "vue";
import {useMainStore} from "../../../store/index.js";

const props = defineProps({
    productId: Number
});

const store = useMainStore();

const visible = ref(false);
const rating = ref(0);
const review = ref('');
const sendStatus = ref(store.requestStatus.notInProcess);
const initialValues = ref({
    review: ''
});

const resolver = ({ values }) => {
    const errors = { review: [] };

    if (!values.review) {
        errors.review.push({ type: 'required', message: 'Отзыв не может быть пустым.' });
    }

    if (values.review?.length < 3) {
        errors.review.push({ type: 'minimum', message: 'Отзыв должен быть не менее 3 символов.' });
    }

    return {
        values,
        errors
    };
};

const onFormSubmit = async ({ valid }) => {
    if (!valid) {
        return;
    }

    const data = {
        product_id: props.productId,
        rating: Number(rating.value),
        text: review.value,
    };

    sendStatus.value = store.requestStatus.inProcess;
    const addResult = await store.sendRequest('/api/reviews/' + props.productId, 'put', data);
    if (addResult === null) {
        sendStatus.value = store.requestStatus.isFailed;

        return;
    }

    sendStatus.value = store.requestStatus.isSuccess;
}

const isRequestInProcess = computed(() => {
    return sendStatus.value === store.requestStatus.inProcess;
});

const isRequestResultSuccess = computed(() => {
    return sendStatus.value === store.requestStatus.isSuccess;
});

const isRequestResultFailed = computed(() => {
    return sendStatus.value === store.requestStatus.isFailed;
});

const hasRequestResult = computed(() => {
    return [store.requestStatus.isSuccess, store.requestStatus.isFailed].includes(sendStatus.value);
});

const addNewReview = () => {
    visible.value = true;
    sendStatus.value = store.requestStatus.notInProcess;
    review.value = '';
    rating.value = 0;
}

</script>

<template>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <Button label="Оставить отзыв" @click="addNewReview"/>
        <Dialog v-model:visible="visible" modal header="Отзыв" :style="{ width: '25rem' }">
            <Form v-if="!hasRequestResult" v-slot="$form" :initialValues :resolver @submit="onFormSubmit"
                  class="flex flex-col gap-4 w-full sm:w-56">
                <div :class="{'content-fade': isRequestInProcess}">
                    <Rating v-model="rating" />
                    <FormField v-slot="$field" name="review" class="flex flex-col gap-1 pt-2">
                        <Textarea placeholder="" v-model="review" name="review" fluid/>
                        <Message v-if="$field?.invalid" severity="error" size="small" variant="simple">
                            {{ $field.error?.message }}
                        </Message>
                    </FormField>
                </div>
                <Button :loading="isRequestInProcess" type="submit" severity="secondary" label="Отправить"/>
            </Form>
            <template v-else>
                <div v-if="isRequestResultSuccess">Отзыв отправлен. Он будет опубликован после подтверждения.</div>
                <div v-if="isRequestResultFailed">Ошибка при добавлении отзыва. Пожалуйста, попробуйте позже.</div>
            </template>
        </Dialog>
    </div>
</template>

<style scoped>

</style>
