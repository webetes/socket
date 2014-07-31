<?php

namespace React\Socket;

use React\Stream\Stream;

class Connection extends Stream implements ConnectionInterface
{
    public function handleData($stream)
    {
        // Socket is raw, not using fread as it's interceptable by filters
        // See issues #192, #209, and #240
        $data = stream_socket_recvfrom($stream, $this->bufferSize);
        if ('' !== $data && false !== $data) {
            $this->emit('data', array($data, $this));
        }

        if ('' === $data || false === $data || !is_resource($stream) || feof($stream)) {
            $this->end();
        }
    }

    public function handleClose()
    {
        if (is_resource($this->stream)) {
            // http://chat.stackoverflow.com/transcript/message/7727858#7727858
            stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
            stream_set_blocking($this->stream, false);
            fclose($this->stream);
        }
    }

    public function getRemoteAddress()
    {
        $rawAddress = stream_socket_get_name($this->stream, true);

        return trim(substr($rawAddress, 0, strrpos($rawAddress, ':')), '[]');
    }

    public function getRemotePort()
    {
        $rawAddress = stream_socket_get_name($this->stream, true);

        return trim(substr($rawAddress, strrpos($rawAddress, ':') + 1), '[]');
    }
}
