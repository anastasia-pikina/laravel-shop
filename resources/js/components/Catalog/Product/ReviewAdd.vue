<script setup>

import Rating from "primevue/rating";
import {Form, FormField} from "@primevue/forms";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Textarea from "primevue/textarea";
import {computed, defineProps, ref} from "vue";
import { useToast } from 'primevue/usetoast';
import axios from "axios";

const props = defineProps({
    productId: Number
});

const visible = ref(false);
const rating = ref(0);
const review = ref('');
const sendStatus = ref('notInProcess');

// const onFormSubmit = () => {
//     const data = {
//         productId: props.productId,
//         rating: rating,
//         review: review,
//     };
//
//     console.log(data)
// };

//const toast = useToast();

const initialValues = ref({
    review: ''
});

const resolver = ({ values }) => {
    const errors = { review: [] };

    if (!values.review) {
        errors.review.push({ type: 'required', message: 'Username is required.' });
    }

    if (values.review?.length < 3) {
        errors.review.push({ type: 'minimum', review: 'Username must be at least 3 characters long.' });
    }

    return {
        values,
        errors
    };
};

const onFormSubmit = async ({ valid }) => {
    // if (valid) {
    //     toast.add({ severity: 'success', summary: 'Form is submitted.', life: 3000 });
    // }

    const data = {
        product_id: props.productId,
        rating: Number(rating.value),
        text: review.value,
    };

    console.log(data)
    sendStatus.value = 'inProcess';
    const response = await axios.put('/api/reviews/' + props.productId, data);
    sendStatus.value = 'inProcessSuccess';
    console.log(data)
}

const inProcess = computed(() => {
    return sendStatus.value === 'inProcess';
});

const inNotProcessSuccess = computed(() => {
    return sendStatus.value !== 'inProcessSuccess';
});

const inProcessSuccess = computed(() => {
    return sendStatus.value === 'inProcessSuccess';
});

const addNewReview = () => {
    visible.value = true;
    sendStatus.value = 'notInProcess';
    review.value = '';
    rating.value = 0;
}

</script>

<template>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <Button label="Оставить отзыв" @click="addNewReview"/>
        <Dialog v-model:visible="visible" modal header="Отзыв" :style="{ width: '25rem' }">
            <Form v-if="inNotProcessSuccess" v-slot="$form" :initialValues :resolver @submit="onFormSubmit"
                  class="flex flex-col gap-4 w-full sm:w-56">
                <Rating v-model="rating" />
                <FormField v-slot="$field" name="details" class="flex flex-col gap-1">
                    <Textarea placeholder="" v-model="review" name="review"/>
                    <Message v-if="$field?.invalid" severity="error" size="small" variant="simple">
                        {{ $field.error?.message }}
                    </Message>
                </FormField>
                <Button :loading="inProcess" type="submit" severity="secondary" label="Отправить"/>
            </Form>
            <div v-if="inProcessSuccess">Отзыв отправлен. Он будет опубликован после подтверждения.</div>
        </Dialog>
    </div>
</template>

<style scoped>

</style>
