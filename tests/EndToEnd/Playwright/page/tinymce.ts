import type {Page} from '@playwright/test'

export async function waitForEditor(page: Page, id: string | null = null): Promise<void> {
    await page.waitForFunction(editorId => {
        const tinymce = (window as any).tinymce
        const editor = editorId === null ? tinymce?.activeEditor : tinymce?.get(editorId)

        return editor?.initialized === true
    }, id)
}

export async function setEditorContent(page: Page, content: string, id: string | null = null): Promise<void> {
    await waitForEditor(page, id)
    await page.evaluate(({editorContent, editorId}) => {
        const tinymce = (window as any).tinymce
        const editor = editorId === null ? tinymce.activeEditor : tinymce.get(editorId)

        editor.setContent(editorContent)
    }, {editorContent: content, editorId: id})
}
